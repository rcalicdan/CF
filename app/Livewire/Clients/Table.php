<?php

namespace App\Livewire\Clients;

use App\ActionService\MonthlyClientReportService;
use App\DataTable\DataTableFactory;
use App\Models\Client;
use App\Traits\Livewire\WithDataTable;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class Table extends Component
{
    use WithDataTable, WithPagination;

    public $selectedMonth = '';
    public $selectedYear = '';

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
                    'key' => 'phone_number',
                    'label' => __('Phone'),
                    'sortable' => true
                ],
                [
                    'key' => 'full_address',
                    'label' => __('Address'),
                    'sortable' => false,
                    'accessor' => true
                ],
                [
                    'key' => 'created_at',
                    'label' => __('Created'),
                    'sortable' => true,
                    'type' => 'datetime'
                ],
            ])
            ->deleteAction('deleteClient')
            ->searchPlaceholder(__('Search clients...'))
            ->emptyMessage(__('No clients found'))
            ->searchQuery($this->search)
            ->sortColumn($this->sortColumn)
            ->sortDirection($this->sortDirection)
            ->showBulkActions(true)
            ->showCreate(true)
            ->viewRoute('clients.show')
            ->createRoute('clients.create')
            ->editRoute('clients.edit')
            ->bulkDeleteAction('bulkDelete');
    }

    public function rowsQuery()
    {
        $query = Client::query();
        $dataTable = $this->getDataTableConfig();

        return $this->applySearchAndSort($query, ['first_name', 'last_name', 'phone_number', 'street_name', 'city'], $dataTable);
    }

    public function getRowsProperty()
    {
        return $this->rowsQuery()->paginate($this->perPage);
    }

    public function getClientsCountProperty()
    {
        if (!$this->selectedMonth || !$this->selectedYear) {
            return 0;
        }

        return Client::whereHas('orders', function ($query) {
            $startOfMonth = Carbon::create($this->selectedYear, $this->selectedMonth, 1)->startOfMonth();
            $endOfMonth = Carbon::create($this->selectedYear, $this->selectedMonth, 1)->endOfMonth();

            $query->whereBetween('created_at', [$startOfMonth, $endOfMonth]);
        })->count();
    }

    public function generateMonthlyPdfReport()
    {
        if (!$this->selectedMonth || !$this->selectedYear) {
            session()->flash('error', 'Proszę wybrać miesiąc i rok.');
            return;
        }

        try {
            $reportService = new MonthlyClientReportService();
            $filename = $reportService->generateMonthlyPdfReport($this->selectedMonth, $this->selectedYear);

            session()->flash('success', 'Raport PDF został pomyślnie wygenerowany.');

            return response()->download(storage_path('app/public/' . $filename));
        } catch (\Exception $e) {
            session()->flash('error', 'Nie udało się wygenerować raportu PDF: ' . $e->getMessage());
        }
    }

    public function generateMonthlyCsvReport()
    {
        if (!$this->selectedMonth || !$this->selectedYear) {
            session()->flash('error', 'Proszę wybrać miesiąc i rok.');
            return;
        }

        try {
            $reportService = new MonthlyClientReportService();
            $filename = $reportService->generateMonthlyCsvReport($this->selectedMonth, $this->selectedYear);

            session()->flash('success', 'Raport CSV został pomyślnie wygenerowany.');

            return response()->download(storage_path('app/public/' . $filename))
                ->deleteFileAfterSend();
        } catch (\Exception $e) {
            session()->flash('error', 'Nie udało się wygenerować raportu CSV: ' . $e->getMessage());
        }
    }

    public function render()
    {
        $this->authorize('viewAny', Client::class);
        $dataTable = $this->getDataTableConfig()->toArray();
        $selectedRowsCount = $this->getSelectedRowsCountProperty();

        return view('livewire.clients.table', [
            'dataTable' => $dataTable,
            'selectedRowsCount' => $selectedRowsCount,
            'clientsCount' => $this->getClientsCountProperty(),
        ]);
    }

    public function bulkDelete()
    {
        $query = Client::query();
        if ($this->selectAll) {
            $query = $this->rowsQuery();
        } else {
            $query->whereIn('id', $this->selectedRows);
        }
        $query->delete();
        $this->clearSelection();

        $this->dispatch('show-message', [
            'message' => 'Wybrani klienci zostali pomyślnie usunięci.',
            'type' => 'success'
        ]);
    }

    public function deleteClient($id)
    {
        $client = Client::findOrFail($id);
        $this->authorize('delete', $client);
        $client->delete();

        $this->dispatch('show-message', [
            'message' => 'Klient został pomyślnie usunięty.',
            'type' => 'success'
        ]);
    }
}
