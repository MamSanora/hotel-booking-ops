<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Transaction Model
 *
 * Records all payment transactions for bookings. Replaces the old Payment
 * model (which had Stripe-specific and ABA PayWay-specific columns) with a
 * simpler design covering the two accepted payment methods: cash and KHQR.
 *
 * A booking can have multiple transactions — e.g. one for the initial
 * booking payment and one or more for stay extensions (Process 5.0 DFD).
 *
 * The 'partial' payment_status supports Process 3.2 ("Confirm Remaining Balance")
 * in the DFD — a guest pays part upfront and the balance on check-in.
 *
 * @property int         $id
 * @property int         $booking_id
 * @property string|null $transaction_id  ABA PayWay reference (legacy, kept for compat)
 * @property string|null $khqr_string     Raw KHQR TLV string shown as the QR code
 * @property string|null $md5_hash        MD5 hash of khqr_string (for Bakong API lookup)
 * @property string|null $tracking_status Last Bakong API payment status
 * @property string|null $apv             ABA PayWay bank approval code
 * @property float       $amount_paid
 * @property string|null $payment_for     'booking' | 'stay_extension'
 * @property string|null $payment_method  'cash' | 'khqr'
 * @property string      $payment_status  'pending'|'partial'|'full'|'refunded'
 */
class Transaction extends Model
{
    use HasFactory, SoftDeletes;

    public const STATUS_PENDING  = 'pending';
    public const STATUS_PARTIAL  = 'partial';
    public const STATUS_FULL     = 'full';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_REFUND_PENDING = 'refund_pending';
    public const STATUS_REFUNDED = 'refunded';

    public const METHOD_CASH     = 'cash';
    public const METHOD_KHQR     = 'khqr';
    public const METHOD_ABA      = 'aba_payway';
    public const METHOD_TELEGRAM = 'aba_telegram';
    public const METHOD_KHQR_ABA = 'khqr_aba';

    public const FOR_BOOKING        = 'booking';
    public const FOR_STAY_EXTENSION = 'stay_extension';

    protected $fillable = [
        'booking_id',
        'transaction_id',
        // Bakong KHQR fields
        'khqr_string',
        'md5_hash',
        'tracking_status',
        // Payment Lock
        'payment_locked_at',
        'payment_lock_expires_at',
        // ABA PayWay fields
        'apv',
        // Payment fields
        'amount_paid',
        'payment_for',
        'payment_method',
        'payment_reference',
        'payment_status',
        // Stay extension metadata (applied to booking after payment confirmed)
        'extension_nights',
        'extension_new_checkout',
    ];

    protected function casts(): array
    {
        return [
            'amount_paid'             => 'decimal:2',
            'payment_locked_at'       => 'datetime',
            'payment_lock_expires_at' => 'datetime',
            'extension_new_checkout'  => 'date',
        ];
    }

    // ── Relationships ──────────────────────────────────────────────────────

    /**
     * The booking this transaction is for.
     */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    // ── Query Scopes ───────────────────────────────────────────────────────

    /**
     * Only fully paid transactions.
     */
    public function scopeSuccessful(Builder $query): Builder
    {
        return $query->whereIn('payment_status', [self::STATUS_FULL, self::STATUS_PARTIAL]);
    }

    /**
     * Only pending (unconfirmed) transactions.
     */
    public function scopePending(Builder $query): Builder
    {
        return $query->where('payment_status', self::STATUS_PENDING);
    }

    // ── Status Helpers ─────────────────────────────────────────────────────

    public function isPending(): bool
    {
        return $this->payment_status === self::STATUS_PENDING;
    }

    public function isPartial(): bool
    {
        return $this->payment_status === self::STATUS_PARTIAL;
    }

    public function isFull(): bool
    {
        return $this->payment_status === self::STATUS_FULL;
    }

    public function isRefunded(): bool
    {
        return $this->payment_status === self::STATUS_REFUNDED;
    }

    public function isKhqr(): bool
    {
        return $this->payment_method === self::METHOD_KHQR;
    }

    public function isCash(): bool
    {
        return $this->payment_method === self::METHOD_CASH;
    }

    // ── Display Helpers ────────────────────────────────────────────────────

    public function displayPaymentMethod(): string
    {
        return match ($this->payment_method) {
            self::METHOD_CASH     => 'Cash',
            self::METHOD_KHQR     => 'Bakong (KHQR)',
            self::METHOD_KHQR_ABA => 'KHQR and ABA Pay',
            self::METHOD_ABA      => 'ABA PayWay',
            self::METHOD_TELEGRAM => 'ABA (Telegram)',
            default               => '—',
        };
    }

    public function displayPaymentFor(): string
    {
        return match ($this->payment_for) {
            self::FOR_BOOKING        => 'Booking',
            self::FOR_STAY_EXTENSION => 'Stay Extension',
            default                  => '—',
        };
    }

    public function displayStatus(): string
    {
        return match ($this->payment_status) {
            self::STATUS_PENDING  => 'Pending',
            self::STATUS_PARTIAL  => 'Partial',
            self::STATUS_FULL     => 'Paid',
            self::STATUS_REFUNDED => 'Refunded',
            default               => ucfirst($this->payment_status),
        };
    }

    /**
     * Returns a Tailwind CSS badge colour class for the current status.
     */
    public function statusBadgeClass(): string
    {
        return match ($this->payment_status) {
            self::STATUS_PENDING  => 'bg-yellow-100 text-yellow-800',
            self::STATUS_PARTIAL  => 'bg-orange-100 text-orange-800',
            self::STATUS_FULL     => 'bg-green-100 text-green-800',
            self::STATUS_REFUND_PENDING => 'bg-purple-100 text-purple-800',
            self::STATUS_REFUNDED => 'bg-red-100 text-red-800',
            default               => 'bg-gray-100 text-gray-600',
        };
    }

    // ── Global Payment Lock ────────────────────────────────────────────────
    // Only one guest may be on the payment page at a time to prevent the
    // Telegram bot's amount-based FIFO fallback from crediting the wrong booking.

    /** Lock duration in minutes — kept very short to detect abandoned sessions quickly. */
    const LOCK_MINUTES = 1;

    /**
     * Returns the active lock transaction (if any non-expired lock exists
     * on a transaction that is NOT the given $ownTransactionId).
     */
    public static function getActiveLockFor(int $ownTransactionId): ?self
    {
        return self::where('payment_lock_expires_at', '>', now())
            ->where('id', '!=', $ownTransactionId)
            ->first();
    }

    /**
     * Acquires the global payment lock for the given transaction.
     * If the transaction already holds the lock, the expiry is renewed.
     */
    public static function acquireLock(int $transactionId): void
    {
        self::where('id', $transactionId)->update([
            'payment_locked_at'       => now(),
            'payment_lock_expires_at' => now()->addMinutes(self::LOCK_MINUTES),
        ]);
    }

    /**
     * Releases the payment lock held by this transaction.
     * Called immediately after payment confirmation so the next guest
     * doesn't wait the full 15 minutes unnecessarily.
     */
    public static function releaseLock(int $transactionId): void
    {
        self::where('id', $transactionId)->update([
            'payment_locked_at'       => null,
            'payment_lock_expires_at' => null,
        ]);
    }

    /**
     * Clears any stale (expired) locks — called by the cleanup cron job.
     */
    public static function clearExpiredLocks(): int
    {
        return self::whereNotNull('payment_lock_expires_at')
            ->where('payment_lock_expires_at', '<=', now())
            ->update([
                'payment_locked_at'       => null,
                'payment_lock_expires_at' => null,
            ]);
    }
}
