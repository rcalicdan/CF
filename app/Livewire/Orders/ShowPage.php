<?php

namespace App\Livewire\Orders;

use App\ActionService\OrderService;
use App\DataTable\DataTableFactory;
use App\Models\Order;
use App\Models\OrderCarpet;
use App\Traits\Livewire\WithDataTable;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class ShowPage extends Component
{
    use WithDataTable, WithPagination;

    public Order $order;
    protected OrderService $orderService;

    public function boot(OrderService $orderService)
    {
        $this->orderService = $orderService;
        $this->deleteAction = 'deleteCarpet';
        $this->routeIdColumn = 'id';
        $this->setDataTableFactory($this->getDataTableConfig());
    }

    public function mount(Order $order)
    {
        $this->order = $order;
        $this->authorize('view', $order);
    }

    private function getDataTableConfig(): DataTableFactory
    {
        $orderId = $this->order->id;
        return DataTableFactory::make()
            ->model(OrderCarpet::class)
            ->registerAccessor('total_price', ['services.total_price'])
            ->registerAccessor('services_count', ['service_count'])
            ->headers([
                [
                    'key' => 'id',
                    'label' => __('Carpet ID'),
                    'sortable' => true
                ],
                [
                    'key' => 'total_area',
                    'label' => __('Area (m²)'),
                    'sortable' => true,
                    'type' => 'decimal',
                    'defaultValue' => '0.00'
                ],
                /*[
                    'key' => 'status',
                    'label' => __('Status'),
                    'sortable' => true,
                    'type' => 'badge',
                    'defaultValue' => __('Pending')
                ],*/
                [
                    'key' => 'services_count',
                    'label' => __('Services'),
                    'sortable' => false,
                    'defaultValue' => '0'
                ],
                [
                    'key' => 'total_price',
                    'label' => __('Total Price'),
                    'sortable' => true,
                    'type' => 'currency',
                    'defaultValue' => '0.00'
                ]
            ])
            ->showActions(true)
            ->showCreate(Auth::user()->can('createCarpet', $this->order))
            ->createRoute("$orderId/carpets/create")
            ->createButtonName(__('Add Carpet'))
            ->viewRoute('order-carpets.show')
            ->editRoute('order-carpets.edit')
            ->deleteAction('deleteCarpet')
            ->searchPlaceholder(__('Search carpets...'))
            ->emptyMessage(__('No carpets found for this order'))
            ->searchQuery($this->search)
            ->sortColumn($this->sortColumn)
            ->sortDirection($this->sortDirection)
            ->showBulkActions(Auth::user()->isAdmin());
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
        $query = OrderCarpet::with([
            'services',
            'orderCarpetPhotos.user',
            'complaint'
        ])->where('order_id', $this->order->id);

        $dataTable = $this->getDataTableConfig();
        $this->applySearchAndSort($query, ['carpet_type', 'status'], $dataTable);

        return $query;
    }

    public function getOrderDetailsProperty()
    {
        return $this->orderService->showOrder($this->order);
    }

    public function deleteCarpet($id)
    {
        $orderCarpet = OrderCarpet::findOrFail($id);
        $this->authorize('delete', $orderCarpet);
        $orderCarpet->delete();

        session()->flash('message', __('Order carpet deleted successfully.'));
    }

    public function render()
    {
        $this->authorize('view', $this->order);
        $dataTable = $this->getDataTableConfig()->toArray();
        $orderDetails = $this->getOrderDetailsProperty();

        return view('livewire.orders.show-page', [
            'dataTable' => $dataTable,
            'orderDetails' => $orderDetails,
        ]);
    }
}
