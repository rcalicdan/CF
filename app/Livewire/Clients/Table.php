<?php

namespace App\Livewire\Clients;

use App\DataTable\DataTableFactory;
use App\Models\Client;
use App\Traits\Livewire\WithDataTable;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class Table extends Component
{
    use WithDataTable, WithPagination;

    public function boot()
    {
        $this->deleteAction = 'deleteClient';
        $this->setDataTableFactory($this->getDataTableConfig());
    }

    private function getDataTableConfig(): DataTableFactory
    {
        return DataTableFactory::make()
            ->model(Client::class)
            ->headers([
                ['key' => 'id', 'label' => __('ID'), 'sortable' => true],
                ['key' => 'full_name', 'label' => __('Full Name'), 'sortable' => true, 'accessor' => true, 'search_columns' => ['first_name', 'last_name'], 'sort_columns' => ['first_name', 'last_name']],
                ['key' => 'email', 'label' => __('Email'), 'sortable' => true, 'defaultValue' => __('No email')],
                ['key' => 'phone_number', 'label' => __('Phone'), 'sortable' => true],
                ['key' => 'full_address', 'label' => __('Address'), 'sortable' => false, 'accessor' => true],
                ['key' => 'created_at', 'label' => __('Created'), 'sortable' => true, 'type' => 'datetime'],
            ])
            ->deleteAction('deleteClient')
            ->searchPlaceholder(__('Search clients...'))
            ->emptyMessage(__('No clients found'))
            ->searchQuery($this->search)
            ->sortColumn($this->sortColumn)
            ->sortDirection($this->sortDirection)
            ->showBulkActions(true)
            ->showCreate(true)
            ->createRoute('clients.create')
            ->editRoute('clients.edit')
            ->bulkDeleteAction('bulkDelete');
    }

    public function rowsQuery()
    {
        $query = Client::query();
        $dataTable = $this->getDataTableConfig();

        return $this->applySearchAndSort($query, ['first_name', 'last_name', 'email', 'phone_number', 'street_name', 'city'], $dataTable);
    }

    public function getRowsProperty()
    {
        return $this->rowsQuery()->paginate($this->perPage);
    }

    public function render()
    {
        $this->authorize('viewAny', Client::class);
        $dataTable = $this->getDataTableConfig()->toArray();
        $selectedRowsCount = $this->getSelectedRowsCountProperty();

        return view('livewire.clients.table', [
            'dataTable' => $dataTable,
            'selectedRowsCount' => $selectedRowsCount,
        ]);
    }

    public function bulkDelete()
    {
        $this->authorize('delete', Client::class);
        $query = Client::query();
        if ($this->selectAll) {
            $query = $this->rowsQuery();
        } else {
            $query->whereIn('id', $this->selectedRows);
        }
        $query->delete();
        $this->clearSelection();

        $this->dispatch('show-message', [
            'message' => __('Selected clients deleted successfully.'),
            'type' => 'success'
        ]);
    }

    public function deleteClient($id)
    {
        $client = Client::findOrFail($id);
        $this->authorize('delete', $client);
        $client->delete();

        $this->dispatch('show-message', [
            'message' => __('Client deleted successfully.'),
            'type' => 'success'
        ]);
    }
}
