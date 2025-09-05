<?php

namespace App\Livewire\Complaints;

use App\DataTable\DataTableFactory;
use App\Models\Complaint;
use App\Traits\Livewire\WithDataTable;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class Table extends Component
{
    use WithDataTable, WithPagination;

    public function boot()
    {
        $this->deleteAction = 'deleteComplaint';
        $this->routeIdColumn = 'id';
        $this->setDataTableFactory($this->getDataTableConfig());
    }

    private function getDataTableConfig(): DataTableFactory
    {
        return DataTableFactory::make()
            ->model(Complaint::class)
            ->headers([
                [
                    'key' => 'id',
                    'label' => __('ID'),
                    'sortable' => true
                ],
                [
                    'key' => 'order_carpet_id',
                    'label' => __('Carpet ID'),
                    'sortable' => true
                ],
                [
                    'key' => 'client_name',
                    'label' => __('Client'),
                    'sortable' => false,
                    'accessor' => true
                ],
                [
                    'key' => 'complaint_details',
                    'label' => __('Complaint Details'),
                    'sortable' => false,
                ],
                [
                    'key' => 'status_label',
                    'label' => __('Status'),
                    'type' => 'badge',
                ],
                [
                    'key' => 'created_at',
                    'label' => __('Created'),
                    'sortable' => true,
                    'type' => 'datetime'
                ],
            ])
            ->deleteAction('deleteComplaint')
            ->searchPlaceholder(__('Search complaints...'))
            ->emptyMessage(__('No complaints found'))
            ->searchQuery($this->search)
            ->sortColumn($this->sortColumn)
            ->sortDirection($this->sortDirection)
            ->showBulkActions(Auth::user()->isAdmin())
            ->showCreate(Auth::user()->can('create', Complaint::class))
            ->createRoute('complaints.create')
            ->editRoute('complaints.edit')
            ->viewRoute('complaints.show')
            ->bulkDeleteAction('bulkDelete');
    }

    public function rowsQuery()
    {
        $query = Complaint::with(['orderCarpet.order.client']);
        
        if (!empty($this->search)) {
            $searchTerm = '%' . $this->search . '%';
            
            $query->where(function ($q) use ($searchTerm) {
                $q->where('complaint_details', 'ilike', $searchTerm)
                  ->orWhere('status', 'ilike', $searchTerm)
                  ->orWhereHas('orderCarpet.order.client', function ($clientQuery) use ($searchTerm) {
                      $clientQuery->where('first_name', 'ilike', $searchTerm)
                                  ->orWhere('last_name', 'ilike', $searchTerm);
                  });
            });
        }
        
        if (!empty($this->sortColumn) && !empty($this->sortDirection)) {
            switch ($this->sortColumn) {
                case 'id':
                case 'order_carpet_id':
                case 'complaint_details':
                case 'status':
                case 'created_at':
                case 'updated_at':
                    $query->orderBy($this->sortColumn, $this->sortDirection);
                    break;
                default:
                    $query->orderBy('created_at', 'desc');
            }
        } else {
            $query->orderBy('created_at', 'desc');
        }

        return $query;
    }

    public function getRowsProperty()
    {
        return $this->rowsQuery()->paginate($this->perPage);
    }

    public function render()
    {
        $this->authorize('viewAny', Complaint::class);
        $dataTable = $this->getDataTableConfig()->toArray();
        $selectedRowsCount = $this->getSelectedRowsCountProperty();

        return view('livewire.complaints.table', [
            'dataTable' => $dataTable,
            'selectedRowsCount' => $selectedRowsCount,
        ]);
    }

    public function bulkDelete()
    {
        $query = Complaint::query();
        if ($this->selectAll) {
            $query = $this->rowsQuery();
        } else {
            $query->whereIn('id', $this->selectedRows);
        }
        $query->delete();
        $this->clearSelection();
        $this->dispatch('show-message', [
            'message' => __('Complaints deleted successfully.'),
            'type' => 'success'
        ]);
    }

    public function deleteComplaint($id)
    {
        $complaint = Complaint::findOrFail($id);
        $this->authorize('delete', $complaint);
        $complaint->delete();

        $this->dispatch('show-message', [
            'message' => __('Complaint deleted successfully.'),
            'type' => 'success'
        ]);
    }
}