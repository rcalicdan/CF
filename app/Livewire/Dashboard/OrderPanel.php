<?php

namespace App\Livewire\Dashboard;

use App\Models\Order;
use App\Enums\OrderStatus;
use Carbon\Carbon;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\On;


class OrderPanel extends Component
{
    use WithPagination;

    public $search = '';
    public $dateRange = 7;
    public $statusFilter = 'all';
    public $perPage = 12;

    protected $queryString = [
        'search' => ['except' => ''],
        'dateRange' => ['except' => 7],
        'statusFilter' => ['except' => 'all'],
    ];

    #[On('driver-assigned')]
    public function refreshOrders()
    {
        $this->resetPage();
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingDateRange()
    {
        $this->resetPage();
    }

    public function updatingStatusFilter()
    {
        $this->resetPage();
    }

    protected function applySearchFilter($query)
    {
        if ($this->search) {
            $query->where(function ($q) {
                $searchTerm = preg_replace('/\s+/', ' ', trim($this->search));
                $q->where('id', 'ILIKE', '%' . $searchTerm . '%')
                    ->orWhereHas('client', function ($clientQuery) use ($searchTerm) {
                        $clientQuery->where(function ($q) use ($searchTerm) {
                            $q->where('first_name', 'ILIKE', '%' . $searchTerm . '%')
                                ->orWhere('last_name', 'ILIKE', '%' . $searchTerm . '%')
                                ->orWhere('street_name', 'ILIKE', '%' . $searchTerm . '%')
                                ->orWhere('city', 'ILIKE', '%' . $searchTerm . '%')
                                ->orWhereRaw(
                                    "REGEXP_REPLACE(first_name || ' ' || last_name, '\\s+', ' ', 'g') ILIKE ?",
                                    ['%' . $searchTerm . '%']
                                )
                                ->orWhereRaw(
                                    "REGEXP_REPLACE(last_name || ' ' || first_name, '\\s+', ' ', 'g') ILIKE ?",
                                    ['%' . $searchTerm . '%']
                                );
                        });
                    });
            });
        }

        return $query;
    }

    public function getStatusCountsProperty()
    {
        $query = Order::query();

        if ($this->dateRange) {
            $query->where('schedule_date', '>=', Carbon::now()->subDays($this->dateRange));
        }

        $query = $this->applySearchFilter($query);

        $allOrders = $query->get();

        return [
            'all' => $allOrders->count(),
            'pending' => $allOrders->where('status', OrderStatus::PENDING->value)->count(),
            'in_progress' => $allOrders->whereIn('status', [
                OrderStatus::ACCEPTED->value,
                OrderStatus::PROCESSING->value
            ])->count(),
            'completed' => $allOrders->whereIn('status', [
                OrderStatus::COMPLETED->value,
                OrderStatus::DELIVERED->value
            ])->count(),
        ];
    }

    public function render()
    {
        $query = Order::with([
            'client',
            'driver.user',
            'orderServices.service',
            'orderCarpets.services',
            'priceList',
            'user',
            'orderPayment',
            'orderDeliveryConfirmation'
        ]);

        $query = $this->applySearchFilter($query);
        $query->when($this->dateRange, function ($query) {
            $query->where('schedule_date', '>=', Carbon::now()->subDays($this->dateRange));
        });

        $query->when($this->statusFilter !== 'all', function ($query) {
            if ($this->statusFilter === 'pending') {
                $query->where('status', OrderStatus::PENDING->value);
            } elseif ($this->statusFilter === 'in_progress') {
                $query->whereIn('status', [
                    OrderStatus::ACCEPTED->value,
                    OrderStatus::PROCESSING->value
                ]);
            } elseif ($this->statusFilter === 'completed') {
                $query->whereIn('status', [
                    OrderStatus::COMPLETED->value,
                    OrderStatus::DELIVERED->value
                ]);
            }
        });

        $query->orderBy('schedule_date', 'desc')
              ->orderBy('created_at', 'desc');

        $orders = $query->paginate($this->perPage);

        return view('livewire.dashboard.order-panel', [
            'orders' => $orders,
            'statusCounts' => $this->statusCounts,
        ]);
    }
}
