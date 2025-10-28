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

class Table extends Component
{
    use WithDataTable, WithPagination;

    protected OrderService $orderService;
    
    public $complaintStatus = null;

    public function boot(OrderService $orderService)
    {
        $this->orderService = $orderService;
        $this->deleteAction = 'deleteOrder';
        $this->routeIdColumn = 'id';
        $this->setDataTableFactory($this->getDataTableConfig());
    }

    public function mount()
    {
        $this->complaintStatus = request()->query('complaint_status');
    }

    public function clearComplaintFilter()
    {
        $this->complaintStatus = null;
        return redirect()->route('orders.index');
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
                    'accesor' => true,
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

        if ($this->complaintStatus) {
            $query->where('is_complaint', true);
            
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

        $dataTable = $this->getDataTableConfig();
        $this->applySearchAndSort($query, ['id', 'status'], $dataTable);

        return $query;
    }

    public function getComplaintStatusLabel()
    {
        if (!$this->complaintStatus) {
            return null;
        }

        return match ($this->complaintStatus) {
            'in_progress' => 'W realizacji',
            'completed' => 'Ukończone',
            OrderStatus::PENDING->value => 'Oczekujące',
            OrderStatus::CANCELED->value => 'Anulowane',
            default => $this->complaintStatus,
        };
    }

    public function render()
    {
        $this->authorize('viewAny', Order::class);
        $dataTable = $this->getDataTableConfig()->toArray();
        $selectedRowsCount = $this->getSelectedRowsCountProperty();

        return view('livewire.orders.table', [
            'dataTable' => $dataTable,
            'selectedRowsCount' => $selectedRowsCount,
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