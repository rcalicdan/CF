<?php

namespace App\Livewire\Orders;

use App\ActionService\OrderService;
use App\DataTable\DataTableFactory;
use App\Enums\UserRoles;
use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\Driver;
use App\Traits\Livewire\WithDataTable;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;
use Carbon\Carbon;

class DriverTable extends Component
{
    use WithDataTable, WithPagination;

    protected OrderService $orderService;

    public $selectedDriverId = '';
    public $selectedDate = '';
    public $dateFrom = '';
    public $dateTo = '';
    public $driverSearch = '';
    public $showDriverDropdown = false;
    public $selectedDriverName = '';
    public $selectedStatus = '';
    public $driverStatus = '';

    public function boot(OrderService $orderService)
    {
        $this->orderService = $orderService;
        $this->deleteAction = 'deleteOrder';
        $this->routeIdColumn = 'id';
        $this->setDataTableFactory($this->getDataTableConfig());
    }

    public function mount()
    {
        if (Auth::user()->isDriver()) {
            $this->selectedDriverId = Auth::user()->driver->id;
            $this->selectedDriverName = Auth::user()->driver->user->full_name;
        }
    }

    public function updatedDriverSearch()
    {
        $this->showDriverDropdown = !empty($this->driverSearch);
    }

    public function selectDriver($driverId, $driverName)
    {
        $this->selectedDriverId = $driverId;
        $this->selectedDriverName = $driverName;
        $this->driverSearch = '';
        $this->showDriverDropdown = false;
        $this->resetPage();
    }

    public function clearDriverSelection()
    {
        $this->selectedDriverId = '';
        $this->selectedDriverName = '';
        $this->driverSearch = '';
        $this->showDriverDropdown = false;
        $this->resetPage();
    }

    public function updatedSelectedStatus()
    {
        $this->resetPage();
    }

    public function updatedDriverStatus()
    {
        $this->resetPage();
    }

    public function getAvailableStatusesProperty()
    {
        return OrderStatus::options();
    }

    public function getFilteredDriversProperty()
    {
        $query = Driver::with('user');

        if (!empty($this->driverSearch)) {
            $query->whereHas('user', function ($q) {
                $q->where('first_name', 'like', '%' . $this->driverSearch . '%')
                    ->orWhere('last_name', 'like', '%' . $this->driverSearch . '%');
            });
        }

        return $query->limit(10)->get();
    }

    public function getRecentDriversProperty()
    {
        return Driver::with('user')
            ->whereHas('orders', function ($query) {
                $query->where('created_at', '>=', now()->subDays(30));
            })
            ->take(5)
            ->get();
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
                    'label' => 'Order ID',
                    'sortable' => true
                ],
                [
                    'key' => 'client_name',
                    'label' => 'Client',
                    'sortable' => true,
                ],
                [
                    'key' => 'driver_name',
                    'label' => 'Driver',
                    'sortable' => true,
                ],
                [
                    'key' => 'status_label',
                    'label' => 'Status',
                    'sortable' => false,
                    'type' => 'badge',
                    'accessor' => true
                ],
                [
                    'key' => 'total_amount',
                    'label' => 'Total Amount',
                    'sortable' => true,
                    'type' => 'currency'
                ],
                [
                    'key' => 'schedule_date',
                    'label' => 'Schedule Date',
                    'sortable' => true,
                    'type' => 'datetime',
                    'defaultValue' => 'Not yet scheduled'
                ],
            ])
            ->deleteAction('deleteOrder')
            ->searchPlaceholder('Search orders...')
            ->emptyMessage('No orders found for selected criteria')
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
        $query = Order::query()
            ->select('orders.*')
            ->with([
                'priceList',
                'client',
                'driver.user',
                'orderCarpets.orderCarpetPhotos.user',
                'orderCarpets.complaint',
                'orderCarpets.services.priceLists',
                'orderDeliveryConfirmation',
                'orderPayment',
            ]);

        if ($this->selectedDriverId) {
            $query->where('assigned_driver_id', $this->selectedDriverId);
        }

        if ($this->selectedDate) {
            $query->whereDate('schedule_date', $this->selectedDate);
        }

        if ($this->dateFrom) {
            $query->whereDate('schedule_date', '>=', $this->dateFrom);
        }

        if ($this->dateTo) {
            $query->whereDate('schedule_date', '<=', $this->dateTo);
        }

        if ($this->selectedStatus) {
            $query->where('status', $this->selectedStatus);
        }

        if ($this->driverStatus === 'active') {
            $query->whereHas('driver.user', fn($q) => $q->where('active', true));
        } elseif ($this->driverStatus === 'inactive') {
            $query->whereHas('driver.user', fn($q) => $q->where('active', false));
        }

        if ($user->role === UserRoles::DRIVER->value) {
            $query->where('assigned_driver_id', $user->driver->id);
        }

        if (!empty($this->search)) {
            $searchTerm = '%' . $this->search . '%';
            $query->where(function ($subQuery) use ($searchTerm) {
                $subQuery->where('orders.id', 'like', $searchTerm)
                    ->orWhereHas('client', function ($q) use ($searchTerm) {
                        $q->where('first_name', 'ilike', $searchTerm)
                            ->orWhere('last_name', 'ilike', $searchTerm)
                            ->orWhereRaw("CONCAT(first_name, ' ', last_name) ILIKE ?", [$searchTerm]);
                    })
                    ->orWhereHas('driver.user', function ($q) use ($searchTerm) {
                        $q->where('first_name', 'ilike', $searchTerm)
                            ->orWhere('last_name', 'ilike', $searchTerm)
                            ->orWhereRaw("CONCAT(first_name, ' ', last_name) ILIKE ?", [$searchTerm]);
                    });
            });
        }

        if (!empty($this->sortColumn)) {
            switch ($this->sortColumn) {
                case 'client_name':
                    $query->leftJoin('clients', 'orders.client_id', '=', 'clients.id')
                        ->orderBy('clients.first_name', $this->sortDirection)
                        ->orderBy('clients.last_name', $this->sortDirection);
                    break;

                case 'driver_name':
                    $query->leftJoin('drivers', 'orders.assigned_driver_id', '=', 'drivers.id')
                        ->leftJoin('users', 'drivers.user_id', '=', 'users.id')
                        ->orderBy('users.first_name', $this->sortDirection)
                        ->orderBy('users.last_name', $this->sortDirection);
                    break;

                case 'status_label':
                    $query->orderBy('status', $this->sortDirection);
                    break;

                default:
                    $query->orderBy($this->sortColumn, $this->sortDirection);
                    break;
            }
        }

        return $query;
    }

    public function updatedSelectedDate()
    {
        $this->resetPage();
    }

    public function updatedDateFrom()
    {
        $this->resetPage();
    }

    public function updatedDateTo()
    {
        $this->resetPage();
    }

    public function clearFilters()
    {
        $this->selectedDriverId = '';
        $this->selectedDriverName = '';
        $this->selectedDate = '';
        $this->dateFrom = '';
        $this->dateTo = '';
        $this->selectedStatus = '';
        $this->driverStatus = '';
        $this->driverSearch = '';
        $this->showDriverDropdown = false;
        $this->resetPage();
    }

    public function setToday()
    {
        $this->selectedDate = now()->format('Y-m-d');
        $this->dateFrom = '';
        $this->dateTo = '';
        $this->resetPage();
    }

    public function setThisWeek()
    {
        $this->selectedDate = '';
        $this->dateFrom = now()->startOfWeek()->format('Y-m-d');
        $this->dateTo = now()->endOfWeek()->format('Y-m-d');
        $this->resetPage();
    }

    public function render()
    {
        $this->authorize('viewAny', Order::class);
        $dataTable = $this->getDataTableConfig()->toArray();
        $selectedRowsCount = $this->getSelectedRowsCountProperty();

        return view('livewire.orders.driver-table', [
            'dataTable' => $dataTable,
            'selectedRowsCount' => $selectedRowsCount,
            'recentDrivers' => $this->getRecentDriversProperty(),
            'filteredDrivers' => $this->getFilteredDriversProperty(),
            'availableStatuses' => $this->getAvailableStatusesProperty(),
        ]);
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
