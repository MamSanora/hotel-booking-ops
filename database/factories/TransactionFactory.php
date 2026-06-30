<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\Transaction;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Transaction>
 *
 * Creates a payment transaction linked to a booking.
 *
 * Usage examples:
 *   // Creates a full payment transaction (auto-creates booking):
 *   Transaction::factory()->paid()->create();
 *
 *   // Linked to an existing booking:
 *   Transaction::factory()->for($booking)->paid()->create();
 */
class TransactionFactory extends Factory
{
    protected $model = Transaction::class;

    public function definition(): array
    {
        return [
            // Auto-creates a linked Booking if not supplied.
            'booking_id'     => Booking::factory(),
            'amount_paid'    => fake()->randomFloat(2, 35, 120),
            'payment_for'    => Transaction::FOR_BOOKING,
            'payment_method' => fake()->randomElement([
                Transaction::METHOD_CASH,
                Transaction::METHOD_KHQR,
            ]),
            'payment_status' => Transaction::STATUS_FULL,
            // Gateway fields — null unless a KHQR payment was initiated.
            'transaction_id'     => null,
            'merchant_reference' => null,
            'payment_link'       => null,
            'qr_code_url'        => null,
        ];
    }

    /**
     * State: payment is pending (KHQR initiated, not yet confirmed).
     */
    public function pending(): static
    {
        return $this->state([
            'payment_status' => Transaction::STATUS_PENDING,
            'amount_paid'    => 0,
            'payment_method' => Transaction::METHOD_KHQR,
        ]);
    }

    /**
     * State: fully paid by cash.
     */
    public function paidCash(): static
    {
        return $this->state([
            'payment_status' => Transaction::STATUS_FULL,
            'payment_method' => Transaction::METHOD_CASH,
        ]);
    }

    /**
     * State: paid via ABA PayWay KHQR with gateway reference data.
     */
    public function paidKhqr(): static
    {
        return $this->state([
            'payment_status'     => Transaction::STATUS_FULL,
            'payment_method'     => Transaction::METHOD_KHQR,
            'transaction_id'     => fake()->uuid(),
            'merchant_reference' => 'DM-' . fake()->numerify('######'),
        ]);
    }

    /**
     * State: partial (half) payment received.
     */
    public function halfPaid(): static
    {
        return $this->state(['payment_status' => Transaction::STATUS_HALF]);
    }

    /**
     * State: transaction for a stay extension rather than the initial booking.
     */
    public function forExtension(): static
    {
        return $this->state(['payment_for' => Transaction::FOR_STAY_EXTENSION]);
    }
}
