<?php

namespace App\Livewire\Orders;

use App\ActionService\OrderService;
use App\DataTable\DataTableFactory;
use App\Models\Order;
use App\Models\OrderCarpet;
use App\Traits\Livewire\WithDataTable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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
        $this->order = $order->load([
            'client',
            'driver.user',
            'orderPayment',
            'orderDeliveryConfirmation',
            'orderHistories.user'
        ]);
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
        $query = OrderCarpet::query()
            ->where('order_id', $this->order->id)
            ->with([
                'services',
                'orderCarpetPhotos.user',
                'complaint'
            ]);

        if (!empty($this->search)) {
            $searchTerm = '%' . $this->search . '%';
            $query->where('carpet_type', 'like', $searchTerm);
        }

        if (!empty($this->sortColumn)) {
            switch ($this->sortColumn) {
                case 'total_price':
                    $query->select('order_carpets.*', DB::raw('SUM(carpet_services.total_price) as calculated_total_price'))
                        ->leftJoin('carpet_services', 'order_carpets.id', '=', 'carpet_services.order_carpet_id')
                        ->groupBy('order_carpets.id')
                        ->orderBy('calculated_total_price', $this->sortDirection);
                    break;

                case 'services_count':
                    $query->select('order_carpets.*', DB::raw('COUNT(carpet_services.id) as calculated_services_count'))
                        ->leftJoin('carpet_services', 'order_carpets.id', '=', 'carpet_services.order_carpet_id')
                        ->groupBy('order_carpets.id')
                        ->orderBy('calculated_services_count', $this->sortDirection);
                    break;

                default:
                    $query->orderBy($this->sortColumn, $this->sortDirection);
                    break;
            }
        }

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

        session()->flash('message', 'Dywan z zamówienia został pomyślnie usunięty.');
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
