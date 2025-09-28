<?php

namespace App\Livewire\LaundryServices;

use App\ActionService\CarpetService;
use App\DataTable\DataTableFactory;
use App\Models\Service;
use App\Traits\Livewire\WithDataTable;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class Table extends Component
{
    use WithDataTable, WithPagination;

    protected CarpetService $carpetService;

    public function boot(CarpetService $carpetService)
    {
        $this->carpetService = $carpetService;
        $this->deleteAction = 'deleteService';
        $this->setDataTableFactory($this->getDataTableConfig());
    }

    private function getDataTableConfig(): DataTableFactory
    {
        return DataTableFactory::make()
            ->model(Service::class)
            ->headers([
                ['key' => 'id', 'label' => __('ID'), 'sortable' => true],
                ['key' => 'name', 'label' => __('Service Name'), 'sortable' => true],
                ['key' => 'base_price', 'label' => __('Base Price'), 'sortable' => true, 'type' => 'currency'],
                ['key' => 'is_area_based', 'label' => __('Area Based'), 'sortable' => true, 'type' => 'boolean'],
                ['key' => 'created_at', 'label' => __('Created'), 'sortable' => true, 'type' => 'datetime'],
            ])
            ->showActions(Auth::user()->isAdmin() || Auth::user()->isEmployee())
            ->deleteAction('deleteService')
            ->searchPlaceholder(__('Search services...'))
            ->emptyMessage(__('No services found'))
            ->searchQuery($this->search)
            ->sortColumn($this->sortColumn)
            ->sortDirection($this->sortDirection)
            ->showBulkActions(Auth::user()->isAdmin())
            ->showCreate(false)
            ->showActions(false);
    }

    public function rowsQuery()
    {
        $query = Service::query();
        $dataTable = $this->getDataTableConfig();

        return $this->applySearchAndSort($query, ['name', 'base_price'], $dataTable);
    }

    public function getRowsProperty()
    {
        return $this->rowsQuery()->paginate($this->perPage);
    }

    public function render()
    {
        $this->authorize('viewAny', Service::class);
        $dataTable = $this->getDataTableConfig()->toArray();
        $selectedRowsCount = $this->getSelectedRowsCountProperty();

        return view('livewire.laundry-services.table', [
            'dataTable' => $dataTable,
            'selectedRowsCount' => $selectedRowsCount,
        ]);
    }

    public function bulkDelete()
    {
        $serviceIds = $this->selectAll ? $this->rowsQuery()->pluck('id')->toArray() : $this->selectedRows;

        foreach ($serviceIds as $id) {
            $this->carpetService->deleteCarpetService($id);
        }

        $this->clearSelection();

        $this->dispatch('show-message', [
            'message' => __('Usługi zostały pomyślnie usunięte.'),
            'type' => 'success'
        ]);
    }

    public function deleteService($id)
    {
        $service = Service::findOrFail($id);
        $this->authorize('delete', $service);

        $this->carpetService->deleteCarpetService($id);

        $this->dispatch('show-message', [
            'message' => __('Usługa została pomyślnie usunięta.'),
            'type' => 'success'
        ]);
    }
}