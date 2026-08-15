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
        return $this->query->with(['guest', 'room.roomType', 'transactions', 'bookingRooms.roomType']);
    }

    public function headings(): array
    {
        return [
            'Booking ID',
            'Reference',
            'Guest Name',
            'Room',
            'Check-In',
            'Check-Out',
            'Total Price (USD)',
            'Status',
            'Payment Status',
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
        
        return [
            $booking->id,
            $booking->referenceNumber(),
            $booking->guest?->full_name ?? 'Walk-in Guest',
            $booking->bookingRooms->isNotEmpty()
                ? $booking->bookingRooms
                    ->groupBy('room_type_id')
                    ->map(function ($rows) {
                        $type = $rows->first()->roomType->display_name ?? $rows->first()->roomType->name;
                        $roomNumbers = $rows->map(fn($br) => $br->room?->room_number ?? 'TBA')->implode(', ');
                        return ($rows->sum('quantity') > 1 ? $rows->sum('quantity') . 'x ' : '') . $type . ' (Rm ' . $roomNumbers . ')';
                    })
                    ->implode(', ')
                : ($booking->room ? ($booking->room->displayType() . ' (Rm ' . $booking->room->room_number . ')') : 'N/A'),
            $booking->check_in_date?->format('Y-m-d'),
            $booking->check_out_date?->format('Y-m-d'),
            (float) $booking->total_price,
            ucfirst($booking->booking_status),
            $paymentStatus,
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
            // Format column G (Total Price) as accounting format with USD symbol
            'G' => '"$"#,##0.00_-',
        ];
    }
}
