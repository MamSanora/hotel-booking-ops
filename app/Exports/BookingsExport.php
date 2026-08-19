<?php

namespace App\Exports;

use App\Models\Booking;
use App\Models\Transaction;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use Illuminate\Database\Eloquent\Builder;

class BookingsExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize, WithStyles, \Maatwebsite\Excel\Concerns\WithColumnFormatting
{
    use Exportable;

    protected Builder $query;

    public function __construct(Builder $query)
    {
        $this->query = $query;
    }

    public function query()
    {
        return $this->query->with(['guest.guestAuth', 'guest.phones', 'bookingRooms.roomType', 'transactions', 'bookingRooms.room']);
    }

    public function headings(): array
    {
        return [
            'Booking ID',
            'Reference',
            'Guest Name',
            'Email',
            'Phone',
            'Origin',
            'Room(s)',
            'Check-In',
            'Check-Out',
            'Nights',
            'Status',
            'Payment Status',
            'Total Charges (USD)',
            'Total Paid (USD)',
            'Balance Due (USD)',
            'Payment Methods',
            'Created At'
        ];
    }

    /**
     * @param Booking $booking
     */
    public function map($booking): array
    {
        $latestTxn = $booking->transactions->sortByDesc('created_at')->first();
        $paymentStatus = $latestTxn ? $latestTxn->displayStatus() : 'Unpaid';
        
        $paymentMethods = $booking->transactions
            ->whereIn('payment_status', ['full', 'partial'])
            ->map(fn($t) => $t->displayPaymentMethod())
            ->filter()
            ->unique()
            ->implode(', ');
            
        $paymentMethods = $paymentMethods ?: '';
        
        return [
            $booking->id,
            $booking->referenceNumber(),
            $booking->guest?->full_name ?? 'Walk-in Guest',
            $booking->guest?->guestAuth?->email ?? '',
            $booking->guest?->phones?->first()?->phone_number ?? '',
            ucfirst($booking->booking_origin),
            $booking->bookingRooms->isNotEmpty()
                ? $booking->bookingRooms
                    ->groupBy('room_type_id')
                    ->map(function ($rows) {
                        $type = $rows->first()->roomType->display_name ?? $rows->first()->roomType->name;
                        $roomNumbers = $rows->map(fn($br) => $br->room?->room_number ?? 'TBA')->implode(', ');
                        return count($rows) > 1 ? count($rows) . 'x ' . $type . ' (Rm ' . $roomNumbers . ')' : $type . ' (Rm ' . $roomNumbers . ')';
                    })
                    ->implode(', ')
                : '',
            $booking->check_in_date?->format('Y-m-d'),
            $booking->check_out_date?->format('Y-m-d'),
            $booking->nightCount(),
            ucfirst($booking->booking_status),
            $paymentStatus,
            (float) $booking->total_price,
            (float) $booking->totalPaid(),
            (float) $booking->balanceDue(),
            $paymentMethods,
            $booking->created_at->format('Y-m-d H:i')
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }

    public function columnFormats(): array
    {
        return [
            // Format column M (Total Charges), N (Total Paid), O (Balance Due) as accounting format with USD symbol
            'M' => '"$"#,##0.00_-',
            'N' => '"$"#,##0.00_-',
            'O' => '"$"#,##0.00_-',
        ];
    }
}
