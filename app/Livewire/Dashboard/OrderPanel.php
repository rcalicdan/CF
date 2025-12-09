<?php

namespace App\Livewire\Dashboard;

use App\Models\Order;
use App\Enums\OrderStatus;
use Carbon\Carbon;
use Livewire\Component;
use Livewire\WithPagination;

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
    protected $listeners = ['driver-assigned' => '$refresh'];

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

    public function getStatusCountsProperty()
    {
        $query = Order::query();
        
        if ($this->dateRange) {
            $query->where('schedule_date', '>=', Carbon::now()->subDays($this->dateRange));
        }

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('id', 'like', '%' . $this->search . '%')
                    ->orWhereHas('client', function ($clientQuery) {
                        $clientQuery->where('first_name', 'like', '%' . $this->search . '%')
                            ->orWhere('last_name', 'like', '%' . $this->search . '%')
                            ->orWhere('street_name', 'like', '%' . $this->search . '%')
                            ->orWhere('city', 'like', '%' . $this->search . '%');
                    });
            });
        }
        
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
        ])
        ->when($this->search, function ($query) {
            $query->where(function ($q) {
                $q->where('id', 'like', '%' . $this->search . '%')
                    ->orWhereHas('client', function ($clientQuery) {
                        $clientQuery->where('first_name', 'like', '%' . $this->search . '%')
                            ->orWhere('last_name', 'like', '%' . $this->search . '%')
                            ->orWhere('street_name', 'like', '%' . $this->search . '%')
                            ->orWhere('city', 'like', '%' . $this->search . '%');
                    });
            });
        })
        ->when($this->dateRange, function ($query) {
            $query->where('schedule_date', '>=', Carbon::now()->subDays($this->dateRange));
        })
        ->when($this->statusFilter !== 'all', function ($query) {
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
        })
        ->orderBy('schedule_date', 'desc')
        ->orderBy('created_at', 'desc');

        $orders = $query->paginate($this->perPage);

        return view('livewire.dashboard.order-panel', [
            'orders' => $orders,
            'statusCounts' => $this->statusCounts,
        ]);
    }
}