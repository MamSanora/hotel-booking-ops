<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\Guest;
use App\Models\Room;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * @extends Factory<Booking>
 *
 * Creates a full booking record. By default it will also auto-create a
 * Guest and a Room if not provided.
 *
 * Usage examples:
 *   // Minimal — creates guest and room automatically:
 *   Booking::factory()->create();
 *
 *   // With specific guest and room:
 *   Booking::factory()->for($guest)->for($room)->create();
 *
 *   // Already checked in:
 *   Booking::factory()->checkedIn()->create();
 */
class BookingFactory extends Factory
{
    protected $model = Booking::class;

    public function definition(): array
    {
        // Build reliable future dates using Carbon — avoids Faker edge cases
        // where start and end collapse to the same datetime.
        $checkIn  = Carbon::now()->addDays(fake()->numberBetween(1, 30));
        $checkOut = $checkIn->copy()->addDays(fake()->numberBetween(1, 7));
        $nights   = $checkIn->diffInDays($checkOut);

        return [
            // Auto-creates a linked Guest if not supplied.
            'guest_id'                 => Guest::factory(),
            // Auto-creates a linked Room if not supplied.
            'room_id'                  => Room::factory(),
            'handled_by_staff_id'      => null,
            'check_in_date'            => $checkIn->toDateString(),
            'check_out_date'           => $checkOut->toDateString(),
            'number_of_stay_extension' => 0,
            // total_price is calculated from a base rate; actual tests should override this.
            'total_price'              => $nights * 40.00,
            'booking_status'           => Booking::STATUS_BOOKED,
            'guest_type'               => Booking::GUEST_TYPE_USER,
        ];
    }

    /**
     * State: booking is still awaiting payment confirmation.
     */
    public function pending(): static
    {
        return $this->state(['booking_status' => Booking::STATUS_PENDING]);
    }

    /**
     * State: guest has checked in.
     */
    public function checkedIn(): static
    {
        return $this->state([
            'booking_status' => Booking::STATUS_CHECKED_IN,
            'check_in_date'  => now()->subDays(1)->format('Y-m-d'),
            'check_out_date' => now()->addDays(2)->format('Y-m-d'),
        ]);
    }

    /**
     * State: guest has checked out.
     */
    public function checkedOut(): static
    {
        return $this->state([
            'booking_status' => Booking::STATUS_CHECKED_OUT,
            'check_in_date'  => now()->subDays(3)->format('Y-m-d'),
            'check_out_date' => now()->subDay()->format('Y-m-d'),
        ]);
    }

    /**
     * State: booking was cancelled.
     */
    public function cancelled(): static
    {
        return $this->state(['booking_status' => Booking::STATUS_CANCELLED]);
    }

    /**
     * State: created by a walk-in via staff (no guest account).
     */
    public function walkIn(): static
    {
        return $this->state([
            'guest_type' => Booking::GUEST_TYPE_WALKIN,
            'guest_id'   => null,
        ]);
    }
}
