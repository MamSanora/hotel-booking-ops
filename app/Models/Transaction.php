<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
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
 * The 'half' payment_status supports Process 3.2 ("Confirm Remaining Balance")
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
 * @property string      $payment_status  'pending'|'half'|'full'|'refunded'
 */
class Transaction extends Model
{
    use HasFactory;

    public const STATUS_PENDING  = 'pending';
    public const STATUS_HALF     = 'half';
    public const STATUS_FULL     = 'full';
    public const STATUS_REFUNDED = 'refunded';

    public const METHOD_CASH    = 'cash';
    public const METHOD_KHQR    = 'khqr';
    public const METHOD_ABA     = 'aba_payway';

    public const FOR_BOOKING        = 'booking';
    public const FOR_STAY_EXTENSION = 'stay_extension';

    protected $fillable = [
        'booking_id',
        'transaction_id',
        'merchant_reference',
        'payment_link',
        'qr_code_url',
        // Bakong KHQR fields
        'khqr_string',
        'md5_hash',
        'tracking_status',
        // ABA PayWay fields
        'apv',
        // Payment fields
        'amount_paid',
        'payment_for',
        'payment_method',
        'payment_status',
    ];

    protected function casts(): array
    {
        return [
            'amount_paid' => 'decimal:2',
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
        return $query->whereIn('payment_status', [self::STATUS_FULL, self::STATUS_HALF]);
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

    public function isHalf(): bool
    {
        return $this->payment_status === self::STATUS_HALF;
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
            self::METHOD_CASH => 'Cash',
            self::METHOD_KHQR => 'Bakong (KHQR)',
            self::METHOD_ABA  => 'ABA PayWay',
            default           => '—',
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
            self::STATUS_HALF     => 'Partial',
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
            self::STATUS_HALF     => 'bg-orange-100 text-orange-800',
            self::STATUS_FULL     => 'bg-green-100 text-green-800',
            self::STATUS_REFUNDED => 'bg-red-100 text-red-800',
            default               => 'bg-gray-100 text-gray-600',
        };
    }
}
