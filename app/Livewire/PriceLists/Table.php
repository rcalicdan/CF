<?php

namespace App\Livewire\PriceLists;

use App\DataTable\DataTableFactory;
use App\Models\PriceList;
use App\Traits\Livewire\WithDataTable;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class Table extends Component
{
    use WithDataTable, WithPagination;

    public function boot()
    {
        $this->deleteAction = 'deletePriceList';
        $this->setDataTableFactory($this->getDataTableConfig());
    }

    private function getDataTableConfig(): DataTableFactory
    {
        return DataTableFactory::make()
            ->model(PriceList::class)
            ->headers([
                ['key' => 'id', 'label' => __('ID'), 'sortable' => true],
                ['key' => 'name', 'label' => __('Name'), 'sortable' => true],
                ['key' => 'location_postal_code', 'label' => __('Postal Code'), 'sortable' => true],
                ['key' => 'created_at', 'label' => __('Created'), 'sortable' => true, 'type' => 'datetime'],
            ])
            ->deleteAction('deletePriceList')
            ->showActions(Auth::user()->isAdmin() || Auth::user()->isEmployee())
            ->searchPlaceholder(__('Search price lists...'))
            ->emptyMessage(__('No price lists found'))
            ->searchQuery($this->search)
            ->sortColumn($this->sortColumn)
            ->sortDirection($this->sortDirection)
            ->showBulkActions(true)
            ->showCreate(Auth::user()->can('create', PriceList::class))
            ->createRoute('price-lists.create')
            ->editRoute('price-lists.edit')
            ->bulkDeleteAction('bulkDelete')
            ->viewRoute('price-lists.view');
    }

    public function rowsQuery()
    {
        $query = PriceList::query();
        $dataTable = $this->getDataTableConfig();

        return $this->applySearchAndSort($query, ['name', 'location_postal_code'], $dataTable);
    }

    public function getRowsProperty()
    {
        return $this->rowsQuery()->paginate($this->perPage);
    }

    public function render()
    {
        $this->authorize('viewAny', PriceList::class);
        $dataTable = $this->getDataTableConfig()->toArray();
        $selectedRowsCount = $this->getSelectedRowsCountProperty();

        return view('livewire.price-lists.table', [
            'dataTable' => $dataTable,
            'selectedRowsCount' => $selectedRowsCount,
        ]);
    }

    /**
     * Check if a single price list can be deleted
     */
    private function canDeletePriceList(PriceList $priceList): bool
    {
        return !$priceList->orders()->exists();
    }

    /**
     * Get price lists that cannot be deleted due to assigned orders
     */
    private function getPriceListsWithOrders($query)
    {
        return $query->clone()->has('orders')->get();
    }

    /**
     * Show error message for single price list deletion
     */
    private function showSingleDeleteError(PriceList $priceList): void
    {
        $this->dispatch('show-message', [
            'message' => 'Nie można usunąć cennika ":name", ponieważ ma przypisane zamówienia.',
            'type' => 'error'
        ]);
    }

    /**
     * Show error message for bulk deletion
     */
    private function showBulkDeleteError($priceListsWithOrders): void
    {
        $names = $priceListsWithOrders->pluck('name')->join(', ');
        $this->dispatch('show-message', [
            'message' => 'Nie można usunąć cenników z przypisanymi zamówieniami: :names',
            'type' => 'error'
        ]);
    }

    /**
     * Show success message for single deletion
     */
    private function showSingleDeleteSuccess(PriceList $priceList): void
    {
        $this->dispatch('show-message', [
            'message' => 'Cennik ":name" został pomyślnie usunięty.',
            'type' => 'success'
        ]);
    }

    /**
     * Show success message for bulk deletion
     */
    private function showBulkDeleteSuccess(int $deletedCount): void
    {
        $this->dispatch('show-message', [
            'message' => 'Pomyślnie usunięto :count cennik(i).',
            'type' => 'success'
        ]);
    }

    public function bulkDelete()
    {
        $query = PriceList::query();
        if ($this->selectAll) {
            $query = $this->rowsQuery();
        } else {
            $query->whereIn('id', $this->selectedRows);
        }

        $priceListsWithOrders = $this->getPriceListsWithOrders($query);

        if ($priceListsWithOrders->isNotEmpty()) {
            $this->showBulkDeleteError($priceListsWithOrders);
            return;
        }

        $deletedCount = $query->delete();
        $this->clearSelection();
        $this->showBulkDeleteSuccess($deletedCount);
    }

    public function deletePriceList($id)
    {
        $priceList = PriceList::findOrFail($id);
        $this->authorize('delete', $priceList);

        if (!$this->canDeletePriceList($priceList)) {
            $this->showSingleDeleteError($priceList);
            return;
        }

        $priceList->delete();
        $this->showSingleDeleteSuccess($priceList);
    }
}
