<?php

namespace App\Livewire\Admin;

use App\Models\Booking;
use App\Models\Transaction;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Url;
use App\Exports\BookingsExport;
use Maatwebsite\Excel\Facades\Excel;

class BookingsList extends Component
{
    use WithPagination;

    #[Url(except: '')]
    public $search = '';

    #[Url(except: '')]
    public $status = '';

    #[Url(except: 'created_at')]
    public $date_type = 'created_at';

    #[Url(except: '')]
    public $date_from = '';

    #[Url(except: '')]
    public $date_to = '';

    #[Url(except: '')]
    public $payment_status = '';

    #[Url(except: '')]
    public $booking_origin = '';

    #[Url(except: 'id')]
    public $sortCol = 'id';

    #[Url(except: 'desc')]
    public $sortDir = 'desc';

    public function sortBy($column)
    {
        if ($this->sortCol === $column) {
            $this->sortDir = $this->sortDir === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortCol = $column;
            $this->sortDir = 'asc';
        }
    }

    public function updating($property)
    {
        // Reset pagination when any filter changes
        if (in_array($property, ['search', 'status', 'date_type', 'date_from', 'date_to', 'booking_origin', 'sortCol', 'sortDir'])) {
            $this->resetPage();
        }
    }

    public function clearFilters()
    {
        $this->reset(['search', 'status', 'date_type', 'date_from', 'date_to', 'booking_origin', 'sortCol', 'sortDir']);
        $this->sortCol = 'id';
        $this->sortDir = 'desc';
        $this->resetPage();
    }

    public function hasAnyFilter()
    {
        return $this->search || $this->status || $this->date_from || $this->date_to || $this->booking_origin || $this->payment_status || $this->sortCol !== 'id' || $this->sortDir !== 'desc';
    }

    protected function buildQuery()
    {
        $query = Booking::with(['guest', 'room', 'handledBy', 'transactions', 'bookingRooms.roomType']);

        // 1. Search by Booking Reference or Guest Name/Email/Phone
        if ($this->search) {
            $query->where(function($q) {
                $search = $this->search;
                $numericSearch = preg_replace('/[^0-9]/', '', $search);
                if (!empty($numericSearch)) {
                    $q->where('id', 'like', "%{$numericSearch}%");
                }
                
                $q->orWhereHas('guest', function($g) use ($search) {
                    $g->where('full_name', 'like', "%{$search}%")
                      ->orWhereHas('guestAuth', function($ga) use ($search) {
                          $ga->where('email', 'like', "%{$search}%");
                      })
                      ->orWhereHas('phones', function($p) use ($search) {
                          $p->where('phone_number', 'like', "%{$search}%");
                      });
                })
                ->orWhereHas('transactions', function($t) use ($search) {
                    $t->where('payment_reference', 'like', "%{$search}%");
                });
            });
        }

        // 2. Filter by Status
        if ($this->status) {
            $query->where('booking_status', $this->status);
        }

        // 3. Filter by Payment Status
        if ($this->payment_status) {
            if ($this->payment_status === 'unpaid') {
                $query->whereDoesntHave('transactions', function($q) {
                    $q->whereIn('payment_status', ['full', 'partial']);
                });
            } elseif ($this->payment_status === 'full') {
                $query->whereHas('transactions', function($q) {
                    $q->where('payment_status', 'full');
                });
            } elseif ($this->payment_status === 'partial') {
                $query->whereHas('transactions', function($q) {
                    $q->where('payment_status', 'partial');
                });
            } elseif ($this->payment_status === 'refunded') {
                $query->whereHas('transactions', function($q) {
                    $q->where('payment_status', 'refunded');
                });
            }
        }

        // 4. Filter by Date Range
        $dateType = $this->date_type;
        $validDateTypes = ['check_in_date', 'check_out_date', 'created_at'];
        if (!in_array($dateType, $validDateTypes)) {
            $dateType = 'created_at';
        }

        if ($this->date_from) {
            $query->whereDate("bookings.{$dateType}", '>=', $this->date_from);
        }
        if ($this->date_to) {
            $query->whereDate("bookings.{$dateType}", '<=', $this->date_to);
        }

        // 4. Filter by Booking Origin
        if ($this->booking_origin) {
            $query->where('booking_origin', $this->booking_origin);
        }

        // 5. Sorting
        if ($this->sortCol === 'guest_name') {
            $query->join('guests', 'bookings.guest_id', '=', 'guests.id')
                  ->orderBy('guests.full_name', $this->sortDir)
                  ->select('bookings.*');
        } else {
            // Ensure valid columns to prevent SQL injection (though it's internal state)
            $validSortCols = ['id', 'check_in_date', 'check_out_date', 'total_price', 'created_at'];
            $col = in_array($this->sortCol, $validSortCols) ? $this->sortCol : 'id';
            $dir = $this->sortDir === 'asc' ? 'asc' : 'desc';
            
            $query->orderBy('bookings.' . $col, $dir);
        }

        return $query;
    }

    public function exportCurrentView()
    {
        $query = $this->buildQuery();
        return $this->generateExport($query);
    }

    public function exportAll()
    {
        $query = Booking::query()->orderBy('id', 'desc');
        return $this->generateExport($query, true);
    }

    protected function generateExport($query, $isAll = false)
    {
        $reportPrefix = match($this->date_type) {
            'check_in_date' => 'arrivals_report',
            'check_out_date' => 'departures_report',
            'created_at' => 'sales_report',
            default => 'bookings_report',
        };

        if ($isAll) {
            $reportPrefix = 'all_bookings_history';
        }

        $filename = $reportPrefix;

        if (!$isAll && $this->status) {
            $filename .= '_' . str_replace(' ', '_', strtolower($this->status));
        }

        if (!$isAll && $this->booking_origin) {
            $filename .= '_' . str_replace(' ', '_', strtolower($this->booking_origin));
        }

        if (!$isAll && $this->date_from && $this->date_to) {
            $start = \Carbon\Carbon::parse($this->date_from);
            $end = \Carbon\Carbon::parse($this->date_to);
            
            if ($start->isSameDay($end)) {
                $filename .= '_daily_' . $start->format('Y-m-d');
            } elseif ($start->copy()->startOfMonth()->isSameDay($start) && $end->copy()->endOfMonth()->isSameDay($end) && $start->isSameMonth($end)) {
                $filename .= '_monthly_' . $start->format('M_Y');
            } elseif ($start->copy()->startOfYear()->isSameDay($start) && $end->copy()->endOfYear()->isSameDay($end) && $start->isSameYear($end)) {
                $filename .= '_yearly_' . $start->format('Y');
            } else {
                $filename .= '_from_' . $start->format('Y-m-d') . '_to_' . $end->format('Y-m-d');
            }
        } elseif (!$isAll && $this->date_from) {
            $filename .= '_from_' . \Carbon\Carbon::parse($this->date_from)->format('Y-m-d');
        } elseif (!$isAll && $this->date_to) {
            $filename .= '_until_' . \Carbon\Carbon::parse($this->date_to)->format('Y-m-d');
        } else {
            $filename .= '_all_time';
        }

        if (!$isAll && $this->payment_status) {
            $statusName = match($this->payment_status) {
                'full' => '_fully_paid',
                'partial' => '_partially_paid',
                'unpaid' => '_unpaid',
                'refunded' => '_refunded',
                default => '',
            };
            $filename .= $statusName;
        }

        if (!$isAll && $this->search) {
            $cleanSearch = preg_replace('/[^A-Za-z0-9]/', '', $this->search);
            $filename .= '_search_' . substr($cleanSearch, 0, 10);
        }

        if (!$isAll) {
            $filename .= '_sort_' . $this->sortCol . '_' . $this->sortDir;
        }

        $filename .= '_' . now()->format('His') . '.xlsx';

        return \Maatwebsite\Excel\Facades\Excel::download(new BookingsExport($query), $filename);
    }

    public function render()
    {
        $bookings = $this->buildQuery()->paginate(20);

        return view('livewire.admin.bookings-list', [
            'bookings' => $bookings
        ]);
    }
}
