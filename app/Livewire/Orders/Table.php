<?php

namespace App\Livewire\Orders;

use App\ActionService\OrderService;
use App\DataTable\DataTableFactory;
use App\Enums\OrderStatus;
use App\Enums\UserRoles;
use App\Models\Order;
use App\Traits\Livewire\WithDataTable;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;
use Carbon\Carbon;

class Table extends Component
{
    use WithDataTable, WithPagination;

    protected OrderService $orderService;

    public $complaintStatus = null;
    public $statusFilter = '';
    public $dateFilter = '';
    public $customStartDate = '';
    public $customEndDate = '';
    public $showAdvancedFilters = false;

    protected function queryString()
    {
        return [
            'search' => ['except' => ''],
            'sortColumn' => ['except' => ''],
            'sortDirection' => ['except' => 'asc'],
            'perPage' => ['except' => 10],
            'statusFilter' => ['except' => ''],
            'dateFilter' => ['except' => ''],
            'complaintStatus' => ['except' => ''],
            'customStartDate' => ['except' => ''],
            'customEndDate' => ['except' => ''],
        ];
    }

    public function boot(OrderService $orderService)
    {
        $this->orderService = $orderService;
        $this->deleteAction = 'deleteOrder';
        $this->routeIdColumn = 'id';
        $this->setDataTableFactory($this->getDataTableConfig());
    }

    public function mount()
    {
        //$this->complaintStatus = request()->query('complaint_status');
        
        $this->complaintStatus = request()->query('complaintStatus');

        if ($this->statusFilter || $this->dateFilter || $this->complaintStatus || $this->customStartDate || $this->customEndDate) {
            $this->showAdvancedFilters = true;
        }
    }

    public function toggleAdvancedFilters()
    {
        $this->showAdvancedFilters = !$this->showAdvancedFilters;
    }

    public function updatedDateFilter()
    {
        if ($this->dateFilter !== 'custom') {
            $this->customStartDate = '';
            $this->customEndDate = '';
        }
        $this->resetPage();
    }

    public function updatedStatusFilter()
    {
        $this->resetPage();
    }

    public function updatedComplaintStatus()
    {
        $this->resetPage();
    }

    public function updatedCustomStartDate()
    {
        $this->resetPage();
    }

    public function updatedCustomEndDate()
    {
        $this->resetPage();
    }

    public function clearFilters()
    {
        $this->statusFilter = '';
        $this->dateFilter = '';
        $this->complaintStatus = null;
        $this->customStartDate = '';
        $this->customEndDate = '';
        $this->resetPage();
    }

    public function clearComplaintFilter()
    {
        $this->complaintStatus = null;
        $this->resetPage();
    }

    private function getDataTableConfig(): DataTableFactory
    {
        return DataTableFactory::make()
            ->model(Order::class)
            ->registerAccessor('client_name', ['client.first_name', 'client.last_name'])
            ->registerAccessor('driver_name', ['driver.user.first_name', 'driver.user.last_name'])
            ->headers([
                [
                    'key' => 'id',
                    'label' => __('Order ID'),
                    'sortable' => true
                ],
                [
                    'key' => 'client_name',
                    'label' => __('Client'),
                    'sortable' => true,
                ],
                [
                    'key' => 'driver_name',
                    'label' => __('Driver'),
                    'sortable' => true,
                ],
                [
                    'key' => 'status_label',
                    'label' => __('Status'),
                    'sortable' => true,
                    'accessor' => true,
                    'search_columns' => ['status'],
                    'sort_columns' => ['status'],
                    'type' => 'badge'
                ],
                [
                    'key' => 'total_amount',
                    'label' => __('Total Amount'),
                    'sortable' => true,
                    'type' => 'currency'
                ],
                [
                    'key' => 'schedule_date',
                    'label' => __('Schedule Date'),
                    'sortable' => true,
                    'type' => 'datetime',
                    'defaultValue' => __('Not yet scheduled')
                ],
            ])
            ->deleteAction('deleteOrder')
            ->searchPlaceholder(__('Search orders...'))
            ->emptyMessage(__('No orders found'))
            ->searchQuery($this->search)
            ->sortColumn($this->sortColumn)
            ->sortDirection($this->sortDirection)
            ->showBulkActions(Auth::user()->isAdmin())
            ->showCreate(Auth::user()->can('create', Order::class))
            ->createRoute('orders.create')
            ->editRoute('orders.edit')
            ->viewRoute('orders.show')
            ->bulkDeleteAction('bulkDelete');
    }

    public function getRowsProperty()
    {
        $query = $this->buildQuery();
        return $query->paginate($this->perPage);
    }

    public function rowsQuery()
    {
        return $this->buildQuery();
    }

    private function buildQuery()
    {
        $user = Auth::user();
        $query = Order::with([
            'priceList',
            'client',
            'driver.user',
            'orderCarpets.orderCarpetPhotos.user',
            'orderCarpets.complaint',
            'orderCarpets.services.priceLists',
            'orderDeliveryConfirmation',
            'orderPayment',
        ]);

        if ($user->role === UserRoles::DRIVER->value) {
            $query->where('assigned_driver_id', $user->driver->id);
        }

        if (!empty($this->statusFilter)) {
            $query->where('status', $this->statusFilter);
        }

        $this->applyDateFilter($query);

        if ($this->complaintStatus) {
            $query->where('is_complaint', true);

            if ($this->complaintStatus !== 'with_complaints') {
                if ($this->complaintStatus === 'in_progress') {
                    $query->whereIn('status', [
                        OrderStatus::ACCEPTED->value,
                        OrderStatus::PROCESSING->value
                    ]);
                } elseif ($this->complaintStatus === 'completed') {
                    $query->whereIn('status', [
                        OrderStatus::COMPLETED->value,
                        OrderStatus::DELIVERED->value
                    ]);
                } else {
                    $query->where('status', $this->complaintStatus);
                }
            }
        }

        if (!empty($this->search)) {
            $searchTerm = '%' . $this->search . '%';
            $query->where(function($query) use ($searchTerm) {
                $query->where('id', 'like', $searchTerm)
                    ->orWhereHas('client', function($q) use ($searchTerm) {
                        $q->where('first_name', 'ilike', $searchTerm)
                            ->orWhere('last_name', 'ilike', $searchTerm)
                            ->orWhereRaw("CONCAT(first_name, ' ', last_name) ILIKE ?", [$searchTerm]);
                    })
                    ->orWhereHas('driver.user', function($q) use ($searchTerm) {
                        $q->where('first_name', 'ilike', $searchTerm)
                            ->orWhere('last_name', 'ilike', $searchTerm)
                            ->orWhereRaw("CONCAT(first_name, ' ', last_name) ILIKE ?", [$searchTerm]);
                    });
            });
        }

        $dataTable = $this->getDataTableConfig();
        if ($this->sortColumn === 'status_label') {
            $query->orderBy('status', $this->sortDirection);
        } else {
            $query->when($this->sortColumn, function($q) {
                $q->orderBy($this->sortColumn, $this->sortDirection);
            });
        }

        return $query;
    }

    private function applyDateFilter($query)
    {
        if (empty($this->dateFilter)) {
            return;
        }

        $now = Carbon::now();

        switch ($this->dateFilter) {
            case 'today':
                $query->whereDate('created_at', $now->toDateString());
                break;

            case 'yesterday':
                $query->whereDate('created_at', $now->subDay()->toDateString());
                break;

            case 'last_7_days':
                $query->whereDate('created_at', '>=', $now->subDays(7)->toDateString());
                break;

            case 'last_30_days':
                $query->whereDate('created_at', '>=', $now->subDays(30)->toDateString());
                break;

            case 'this_month':
                $query->whereMonth('created_at', $now->month)
                    ->whereYear('created_at', $now->year);
                break;

            case 'last_month':
                $lastMonth = $now->subMonth();
                $query->whereMonth('created_at', $lastMonth->month)
                    ->whereYear('created_at', $lastMonth->year);
                break;

            case 'this_year':
                $query->whereYear('created_at', $now->year);
                break;

            case 'custom':
                if ($this->customStartDate) {
                    $query->whereDate('created_at', '>=', $this->customStartDate);
                }
                if ($this->customEndDate) {
                    $query->whereDate('created_at', '<=', $this->customEndDate);
                }
                break;
        }
    }

    public function getComplaintStatusLabel()
    {
        if (!$this->complaintStatus) {
            return null;
        }

        return match ($this->complaintStatus) {
            'with_complaints' => 'Zamówienia z reklamacjami',
            'in_progress' => 'W trakcie realizacji',
            'completed' => 'Zakończony',
            OrderStatus::PENDING->value => 'W oczekiwaniu',
            OrderStatus::CANCELED->value => 'Anulowany',
            default => $this->complaintStatus,
        };
    }

    public function getActiveFiltersCountProperty()
    {
        $count = 0;
        if ($this->statusFilter) $count++;
        if ($this->dateFilter) $count++;
        if ($this->complaintStatus) $count++;
        return $count;
    }

    public function render()
    {
        $this->authorize('viewAny', Order::class);
        $dataTable = $this->getDataTableConfig()->toArray();
        $selectedRowsCount = $this->getSelectedRowsCountProperty();

        return view('livewire.orders.table', [
            'dataTable' => $dataTable,
            'selectedRowsCount' => $selectedRowsCount,
            'orderStatuses' => OrderStatus::cases(),
            'complaintStatuses' => $this->getComplaintStatusOptions(),
        ]);
    }

    private function getComplaintStatusOptions()
    {
        return [
            '' => 'Wszystkie zamówienia',
            'with_complaints' => 'Zamówienia z reklamacjami',
            'in_progress' => 'W trakcie',
            'completed' => 'Zakończone',
            OrderStatus::PENDING->value => 'Oczekujące',
            OrderStatus::CANCELED->value => 'Anulowane',
        ];
    }

    public function bulkDelete()
    {
        $query = Order::query();
        if ($this->selectAll) {
            $query = $this->rowsQuery();
        } else {
            $query->whereIn('id', $this->selectedRows);
        }
        $query->delete();
        $this->clearSelection();

        $this->dispatch('show-message', [
            'message' => 'Zamówienia zostały pomyślnie usunięte.',
            'type' => 'success'
        ]);
    }

    public function deleteOrder($id)
    {
        $order = Order::findOrFail($id);
        $this->authorize('delete', $order);
        $order->delete();

        $this->dispatch('show-message', [
            'message' => 'Zamówienie zostało pomyślnie usunięte.',
            'type' => 'success'
        ]);
    }
}
