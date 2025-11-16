<?php

namespace App\Livewire\Users;

use App\DataTable\DataTableFactory;
use App\Models\User;
use App\Traits\Livewire\WithDataTable;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class Table extends Component
{
    use WithDataTable, WithPagination;

    public $statusFilter = 'active';

    public function boot()
    {
        $this->deleteAction = 'deleteUser';
        $this->routeIdColumn = 'id';
        $this->setDataTableFactory($this->getDataTableConfig());
    }

    private function getDataTableConfig(): DataTableFactory
    {
        return DataTableFactory::make()
            ->model(User::class)
            ->headers([
                [
                    'key' => 'id',
                    'label' => __('ID'),
                    'sortable' => true
                ],
                [
                    'key' => 'full_name',
                    'label' => __('Full Name'),
                    'sortable' => true,
                    'accessor' => true,
                    'search_columns' => ['first_name', 'last_name'],
                    'sort_columns' => ['first_name', 'last_name']
                ],
                [
                    'key' => 'email',
                    'label' => __('Email'),
                    'sortable' => true
                ],
                [
                    'key' => 'role_label',
                    'label' => __('Role'),
                    'sortable' => true,
                    'accessor' => true,
                    'search_columns' => ['role'],
                    'sort_columns' => ['role'],
                    'type' => 'badge'
                ],
                [
                    'key' => 'created_at',
                    'label' => __('Created'),
                    'sortable' => true,
                    'type' => 'datetime'
                ],
            ])
            ->deleteAction('deleteUser')
            ->searchPlaceholder(__('Search users...'))
            ->emptyMessage(__('No users found'))
            ->searchQuery($this->search)
            ->sortColumn($this->sortColumn)
            ->sortDirection($this->sortDirection)
            ->showBulkActions(Auth::user()->isAdmin())
            ->showCreate(Auth::user()->can('create', User::class))
            ->createRoute('users.create')
            ->editRoute('users.edit')
            ->bulkDeleteAction('bulkDelete');
    }

    public function rowsQuery()
    {
        $query = User::query();

        // Apply status filter
        if ($this->statusFilter === 'active') {
            $query->active();
        } elseif ($this->statusFilter === 'inactive') {
            $query->inactive();
        }
        // 'all' means no filter applied

        $dataTable = $this->getDataTableConfig();

        return $this->applySearchAndSort($query, ['first_name', 'last_name', 'email', 'role'], $dataTable);
    }

    public function updatedStatusFilter()
    {
        $this->resetPage();
    }

    public function canDeleteRow($row): bool
    {
        // Nie pozwalaj na usuwanie nieaktywnych użytkowników
        if ($row->isInactive()) {
            return false;
        }

        // Sprawdź standardowe uprawnienia przez DataTableFactory
        return $this->dataTableFactory ? $this->dataTableFactory->canDelete($row) : true;
    }

    public function getRowsProperty()
    {
        return $this->rowsQuery()->paginate($this->perPage);
    }

    public function render()
    {
        $this->authorize('viewAny', User::class);
        $dataTable = $this->getDataTableConfig()->toArray();
        $selectedRowsCount = $this->getSelectedRowsCountProperty();

        return view('livewire.users.table', [
            'dataTable' => $dataTable,
            'selectedRowsCount' => $selectedRowsCount,
        ]);
    }

    public function bulkDelete()
    {
        $query = User::query();
        if ($this->selectAll) {
            $query = $this->rowsQuery();
        } else {
            $query->whereIn('id', $this->selectedRows);
        }

        $query->update(['active' => false]);

        $this->clearSelection();
        $this->dispatch('show-message', [
            'message' => __('Users have been successfully deactivated.'),
            'type' => 'success'
        ]);
    }

    public function deleteUser($id)
    {
        $user = User::findOrFail($id);
        $this->authorize('delete', $user);

        $user->deactivate();

        $this->dispatch('show-message', [
            'message' => __('User has been successfully deactivated.'),
            'type' => 'success'
        ]);
    }

    public function activateUser($id)
    {
        $user = User::findOrFail($id);
        $this->authorize('update', $user);

        $user->activate();

        $this->dispatch('show-message', [
            'message' => __('User has been successfully activated.'),
            'type' => 'success'
        ]);
    }
}
