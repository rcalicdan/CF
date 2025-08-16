<?php

namespace App\Livewire\ServicePriceLists;

use App\DataTable\DataTableFactory;
use App\Models\ServicePriceList;
use App\Traits\Livewire\WithDataTable;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class Table extends Component
{
    use WithDataTable, WithPagination;

    public function boot()
    {
        $this->deleteAction = 'deleteServicePriceList';
        $this->setDataTableFactory($this->getDataTableConfig());
    }

    private function getDataTableConfig(): DataTableFactory
    {
        return DataTableFactory::make()
            ->model(ServicePriceList::class)
            ->headers([
                ['key' => 'id', 'label' => __('ID'), 'sortable' => true],
                ['key' => 'price_list_name', 'label' => __('Price List'), 'sortable' => true, 'accessor' => true, 'search_columns' => ['priceList.name'], 'sort_columns' => ['priceList.name']],
                ['key' => 'service_name', 'label' => __('Service'), 'sortable' => true, 'accessor' => true, 'search_columns' => ['service.name'], 'sort_columns' => ['service.name']],
                ['key' => 'price', 'label' => __('Price'), 'sortable' => true, 'type' => 'currency'],
                ['key' => 'price_comparison', 'label' => __('vs Base Price'), 'accessor' => true, 'type' => 'badge'],
                ['key' => 'created_at', 'label' => __('Created'), 'sortable' => true, 'type' => 'datetime'],
            ])
            ->deleteAction('deleteServicePriceList')
            ->searchPlaceholder(__('Search service prices...'))
            ->emptyMessage(__('No service prices found'))
            ->searchQuery($this->search)
            ->showActions(Auth::user()->isAdmin() || Auth::user()->isEmployee())
            ->sortColumn($this->sortColumn)
            ->sortDirection($this->sortDirection)
            ->showBulkActions(Auth::user()->isAdmin())
            ->showCreate(Auth::user()->can('create', ServicePriceList::class))
            ->createRoute('service-price-lists.create')
            ->editRoute('service-price-lists.edit')
            ->bulkDeleteAction('bulkDelete');
    }

    public function rowsQuery()
    {
        $query = ServicePriceList::with(['priceList', 'service']);
        $dataTable = $this->getDataTableConfig();

        return $this->applySearchAndSort($query, [
            'price',
            'priceList.name',
            'service.name',
            'priceList.location_postal_code'
        ], $dataTable);
    }

    public function getRowsProperty()
    {
        return $this->rowsQuery()->paginate($this->perPage);
    }

    public function render()
    {
        $this->authorize('viewAny', ServicePriceList::class);
        $dataTable = $this->getDataTableConfig()->toArray();
        $selectedRowsCount = $this->getSelectedRowsCountProperty();

        return view('livewire.service-price-lists.table', [
            'dataTable' => $dataTable,
            'selectedRowsCount' => $selectedRowsCount,
        ]);
    }

    public function bulkDelete()
    {
        $query = ServicePriceList::query();
        if ($this->selectAll) {
            $query = $this->rowsQuery();
        } else {
            $query->whereIn('id', $this->selectedRows);
        }
        $query->delete();
        $this->clearSelection();

        $this->dispatch('show-message', [
            'message' => __('Service prices deleted successfully.'),
            'type' => 'success'
        ]);
    }

    public function deleteServicePriceList($id)
    {
        $servicePriceList = ServicePriceList::findOrFail($id);
        $this->authorize('delete', $servicePriceList);
        $servicePriceList->delete();

        $this->dispatch('show-message', [
            'message' => __('Service price deleted successfully.'),
            'type' => 'success'
        ]);
    }
}
