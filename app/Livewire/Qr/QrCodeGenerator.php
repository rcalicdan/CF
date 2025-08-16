<?php

namespace App\Livewire\Qr;

use App\ActionService\QrCodeService;
use Livewire\Component;
use Livewire\WithPagination;

class QrCodeGenerator extends Component
{
    use WithPagination;

    public $isGenerating = false;
    public $selectedQrCodes = [];
    public $selectAll = false;
    public $perPage = 12;
    public $search = '';
    public $filter = 'unassigned';
    public $currentPage = 1;

    protected $listeners = ['qr-assigned' => '$refresh'];
    protected $qrCodeService;

    public function boot(QrCodeService $qrCodeService)
    {
        $this->qrCodeService = $qrCodeService;
    }

    public function mount()
    {
        $this->selectedQrCodes = [];
        $this->currentPage = 1;
    }

    public function nextPage()
    {
        $paginatedData = $this->getPaginatedData();
        if ($this->currentPage < $paginatedData['last_page']) {
            $this->currentPage++;
            $this->selectedQrCodes = [];
            $this->selectAll = false;
        }
    }

    public function previousPage()
    {
        if ($this->currentPage > 1) {
            $this->currentPage--;
            $this->selectedQrCodes = [];
            $this->selectAll = false;
        }
    }

    public function gotoPage($page)
    {
        $paginatedData = $this->getPaginatedData();
        if ($page >= 1 && $page <= $paginatedData['last_page']) {
            $this->currentPage = $page;
            $this->selectedQrCodes = [];
            $this->selectAll = false;
        }
    }

    public function updatedSearch()
    {
        $this->currentPage = 1;
        $this->selectedQrCodes = [];
        $this->selectAll = false;
    }

    public function updatedFilter()
    {
        $this->currentPage = 1;
        $this->selectedQrCodes = [];
        $this->selectAll = false;
    }

    public function updatedPerPage()
    {
        $this->currentPage = 1;
        $this->selectedQrCodes = [];
        $this->selectAll = false;
    }

    private function getPaginatedData(): array
    {
        return $this->qrCodeService->getPaginatedQrCodes(
            $this->filter,
            $this->search,
            $this->perPage,
            $this->currentPage
        );
    }

    public function generateQrCode()
    {
        try {
            $this->isGenerating = true;
            $this->qrCodeService->generateQrCode();

            $this->dispatch('qr-generated', ['message' => 'Kod QR został pomyślnie wygenerowany!']);
            $this->dispatch('show-message', [
                'message' => 'Kod QR został pomyślnie wygenerowany!',
                'type' => 'success'
            ]);
        } catch (\Exception $e) {
            $this->dispatch('qr-error', ['message' => $e->getMessage()]);
            $this->dispatch('show-message', [
                'message' => $e->getMessage(),
                'type' => 'error'
            ]);
        } finally {
            $this->isGenerating = false;
        }
    }

    public function generateBulkQrCodes($count = 5)
    {
        try {
            $this->isGenerating = true;
            $this->qrCodeService->generateBulkQrCodes($count);

            $this->dispatch('qr-generated', ['message' => "Pomyślnie wygenerowano {$count} kodów QR!"]);
            $this->dispatch('show-message', [
                'message' => "Pomyślnie wygenerowano {$count} kodów QR!",
                'type' => 'success'
            ]);
        } catch (\Exception $e) {
            $this->dispatch('qr-error', ['message' => $e->getMessage()]);
            $this->dispatch('show-message', [
                'message' => $e->getMessage(),
                'type' => 'error'
            ]);
        } finally {
            $this->isGenerating = false;
        }
    }

    public function toggleSelectAll()
    {
        $currentPageData = $this->getPaginatedData();
        $currentPageQrCodes = $currentPageData['data'];

        if ($this->selectAll) {
            $currentPageReferences = array_column($currentPageQrCodes, 'reference_code');
            $this->selectedQrCodes = array_unique(array_merge($this->selectedQrCodes, $currentPageReferences));
        } else {
            $currentPageReferences = array_column($currentPageQrCodes, 'reference_code');
            $this->selectedQrCodes = array_diff($this->selectedQrCodes, $currentPageReferences);
        }
    }

    public function deleteSelected()
    {
        if (empty($this->selectedQrCodes)) {
            return;
        }

        $deletedCount = $this->qrCodeService->deleteQrCodes($this->selectedQrCodes);

        if ($deletedCount > 0) {
            $this->selectedQrCodes = [];
            $this->selectAll = false;

            $this->dispatch('qr-deleted', ['message' => "Pomyślnie usunięto {$deletedCount} kodów QR!"]);
            $this->dispatch('show-message', [
                'message' => "Pomyślnie usunięto {$deletedCount} kodów QR!",
                'type' => 'success'
            ]);
        } else {
            $this->dispatch('qr-error', ['message' => 'Nie usunięto żadnych kodów QR. Mogą być przypisane do dywanów.']);
            $this->dispatch('show-message', [
                'message' => 'Nie usunięto żadnych kodów QR. Mogą być przypisane do dywanów.',
                'type' => 'error'
            ]);
        }
    }

    public function deleteQrCode($referenceCode)
    {
        $deletedCount = $this->qrCodeService->deleteQrCodes([$referenceCode]);

        if ($deletedCount > 0) {
            $this->dispatch('qr-deleted', ['message' => 'Kod QR został pomyślnie usunięty!']);
            $this->dispatch('show-message', [
                'message' => 'Kod QR został pomyślnie usunięty!',
                'type' => 'success'
            ]);
        } else {
            $this->dispatch('qr-error', ['message' => 'Nie można usunąć kodu QR. Może być przypisany do dywanu.']);
            $this->dispatch('show-message', [
                'message' => 'Nie można usunąć kodu QR. Może być przypisany do dywanu.',
                'type' => 'error'
            ]);
        }
    }

    public function updatedSelectedQrCodes()
    {
        $this->selectAll = false;
    }

    public function printSelected(int $copies = 1)
    {
        if (empty($this->selectedQrCodes)) {
            return;
        }

        $qrCodesToPrint = $this->qrCodeService->getQrCodesByReferences($this->selectedQrCodes);

        if (empty($qrCodesToPrint)) {
            $this->dispatch('qr-error', ['message' => 'Nie można znaleźć wybranych kodów QR do wydruku.']);
            $this->dispatch('show-message', [
                'message' => 'Nie można znaleźć wybranych kodów QR do wydruku.',
                'type' => 'error'
            ]);
            return;
        }

        $html = '';
        foreach ($qrCodesToPrint as $qrCode) {
            $itemHtml = view('livewire.qr.partials.qr-code-print-item', ['qrCode' => $qrCode])->render();
            if ($copies > 0) {
                $html .= str_repeat($itemHtml, $copies);
            }
        }

        $this->dispatch('print-qr-codes', content: $html);
    }

    public function render()
    {
        $stats = $this->qrCodeService->getQrCodeStats();
        $paginatedData = $this->getPaginatedData();

        return view('livewire.qr.qr-code-generator', [
            'qrCodes' => $paginatedData['data'],
            'totalUnassigned' => $stats['unassigned'],
            'totalAssigned' => $stats['assigned'],
            'totalAll' => $stats['total'],
            'total' => $paginatedData['total'],
            'currentPage' => $paginatedData['current_page'],
            'lastPage' => $paginatedData['last_page'],
            'from' => $paginatedData['from'],
            'to' => $paginatedData['to'],
            'hasQrCodes' => !empty($paginatedData['data']),
        ]);
    }
}
