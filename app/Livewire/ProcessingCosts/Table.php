<?php

namespace App\Livewire\ProcessingCosts;

use App\DataTable\DataTableFactory;
use App\Models\ProcessingCost;
use App\Enums\CostType;
use App\Traits\Livewire\WithDataTable;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;
use Carbon\Carbon;

class Table extends Component
{
    use WithDataTable, WithPagination;

    public $selectedName = '';
    public $selectedType = '';
    public $selectedDate = '';
    public $dateFrom = '';
    public $dateTo = '';
    public $selectedMonth = '';

    public function boot()
    {
        $this->deleteAction = 'deleteProcessingCost';
        $this->routeIdColumn = 'id';
        $this->setDataTableFactory($this->getDataTableConfig());
    }

    public function mount()
    {
        // Set default month to current month
        $this->selectedMonth = now()->format('Y-m');
    }

    private function getDataTableConfig(): DataTableFactory
    {
        return DataTableFactory::make()
            ->model(ProcessingCost::class)
            ->headers([
                [
                    'key' => 'id',
                    'label' => __('ID'),
                    'sortable' => true
                ],
                [
                    'key' => 'name',
                    'label' => __('Name'),
                    'sortable' => true
                ],
                [
                    'key' => 'type_label',
                    'label' => __('Type'),
                    'sortable' => true,
                    'accessor' => true,
                    'type' => 'badge'
                ],
                [
                    'key' => 'amount',
                    'label' => __('Amount'),
                    'sortable' => true,
                    'type' => 'currency'
                ],
                [
                    'key' => 'cost_date',
                    'label' => __('Cost Date'),
                    'sortable' => true,
                    'type' => 'date'
                ],
                [
                    'key' => 'created_at',
                    'label' => __('Created'),
                    'sortable' => true,
                    'type' => 'datetime'
                ],
            ])
            ->deleteAction('deleteProcessingCost')
            ->searchPlaceholder(__('Search processing costs...'))
            ->emptyMessage(__('No processing costs found'))
            ->searchQuery($this->search)
            ->sortColumn($this->sortColumn)
            ->sortDirection($this->sortDirection)
            ->showBulkActions(true)
            ->showCreate(true)
            ->createRoute('processing-costs.create')
            ->editRoute('processing-costs.edit')
            ->bulkDeleteAction('bulkDelete');
    }

    public function rowsQuery()
    {
        return $this->buildQuery();
    }

    public function getRowsProperty()
    {
        return $this->buildQuery()->paginate($this->perPage);
    }

    private function buildQuery()
    {
        $query = ProcessingCost::query();

        // Apply filters
        if ($this->selectedName) {
            $query->where('name', 'like', '%' . $this->selectedName . '%');
        }

        if ($this->selectedType) {
            $query->where('type', $this->selectedType);
        }

        if ($this->selectedDate) {
            $query->whereDate('cost_date', $this->selectedDate);
        }

        if ($this->dateFrom) {
            $query->whereDate('cost_date', '>=', $this->dateFrom);
        }

        if ($this->dateTo) {
            $query->whereDate('cost_date', '<=', $this->dateTo);
        }

        if ($this->selectedMonth) {
            $query->whereYear('cost_date', Carbon::parse($this->selectedMonth)->year)
                ->whereMonth('cost_date', Carbon::parse($this->selectedMonth)->month);
        }

        // Apply search and sort
        $dataTable = $this->getDataTableConfig();
        return $this->applySearchAndSort($query, ['name', 'type'], $dataTable);
    }

    // Filter update methods
    public function updatedSelectedName()
    {
        $this->resetPage();
    }

    public function updatedSelectedType()
    {
        $this->resetPage();
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

    public function updatedSelectedMonth()
    {
        $this->resetPage();
    }

    // Quick filter methods
    public function setToday()
    {
        $this->selectedDate = now()->format('Y-m-d');
        $this->dateFrom = '';
        $this->dateTo = '';
        $this->selectedMonth = '';
        $this->resetPage();
    }

    public function setThisWeek()
    {
        $this->selectedDate = '';
        $this->dateFrom = now()->startOfWeek()->format('Y-m-d');
        $this->dateTo = now()->endOfWeek()->format('Y-m-d');
        $this->selectedMonth = '';
        $this->resetPage();
    }

    public function setThisMonth()
    {
        $this->selectedDate = '';
        $this->dateFrom = '';
        $this->dateTo = '';
        $this->selectedMonth = now()->format('Y-m');
        $this->resetPage();
    }

    public function clearFilters()
    {
        $this->selectedName = '';
        $this->selectedType = '';
        $this->selectedDate = '';
        $this->dateFrom = '';
        $this->dateTo = '';
        $this->selectedMonth = '';
        $this->resetPage();
    }

    public function getAvailableTypesProperty()
    {
        return CostType::options();
    }

    public function render()
    {
        $dataTable = $this->getDataTableConfig()->toArray();
        $selectedRowsCount = $this->getSelectedRowsCountProperty();

        return view('livewire.processing-costs.table', [
            'dataTable' => $dataTable,
            'selectedRowsCount' => $selectedRowsCount,
            'availableTypes' => $this->getAvailableTypesProperty(),
        ]);
    }

    public function bulkDelete()
    {
        $query = ProcessingCost::query();
        if ($this->selectAll) {
            $query = $this->rowsQuery();
        } else {
            $query->whereIn('id', $this->selectedRows);
        }
        $query->delete();
        $this->clearSelection();
        $this->dispatch('show-message', [
            'message' => 'Koszty przetwarzania zostały pomyślnie usunięte.',
            'type' => 'success'
        ]);
    }

    public function deleteProcessingCost($id)
    {
        $processingCost = ProcessingCost::findOrFail($id);
        $processingCost->delete();

        $this->dispatch('show-message', [
            'message' => 'Koszt przetwarzania został pomyślnie usunięty.',
            'type' => 'success'
        ]);
    }
}
