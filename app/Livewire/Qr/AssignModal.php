<?php

namespace App\Livewire\Qr;

use App\ActionService\QrCodeService;
use App\Models\OrderCarpet;
use Livewire\Component;

class AssignModal extends Component
{
    public $showModal = false;
    public $qrCodeReference;
    public $searchTerm = '';
    public $carpets = [];
    public $allUnassignedCarpets = [];
    public $selectedCarpet;
    public $showDropdown = false;
    protected $listeners = ['openAssignModal'];

    public function openAssignModal($qrCodeReference)
    {
        $this->qrCodeReference = $qrCodeReference;
        $this->reset(['searchTerm', 'carpets', 'selectedCarpet']);
        $this->showModal = true;
        $this->loadInitialUnassignedCarpets();
    }

    private function loadInitialUnassignedCarpets()
    {
        $this->allUnassignedCarpets = OrderCarpet::whereNull('qr_code')
            ->with('order.client:id,first_name,last_name')
            ->limit(20)
            ->get()
            ->map(function ($carpet) {
                $client = $carpet->order->client ?? null;
                $fullName = $client ? trim($client->first_name . ' ' . $client->last_name) : 'N/A';
                return [
                    'id' => $carpet->id,
                    'order_id' => $carpet->order_id,
                    'identifier' => '#' . $carpet->id,
                    'order' => [
                        'id' => $carpet->order->id ?? null,
                        'client' => [
                            'id' => $client->id ?? null,
                            'full_name' => $fullName
                        ]
                    ]
                ];
            })
            ->toArray();
        $this->carpets = $this->allUnassignedCarpets;
    }

    public function updatedSearchTerm()
    {
        if (strlen($this->searchTerm) > 2) {
            $searchTerm = $this->searchTerm;
            $this->carpets = OrderCarpet::whereNull('qr_code')
                ->where(function ($query) use ($searchTerm) {
                    $query->where('id', 'like', '%' . $searchTerm . '%')
                        ->orWhereHas('order.client', function ($q) use ($searchTerm) {
                            $q->where('first_name', 'ilike', '%' . $searchTerm . '%')
                                ->orWhere('last_name', 'ilike', '%' . $searchTerm . '%');
                        });
                })
                ->with('order.client:id,first_name,last_name')
                ->limit(10)
                ->get()
                ->map(function ($carpet) {
                    $client = $carpet->order->client ?? null;
                    $fullName = $client ? trim($client->first_name . ' ' . $client->last_name) : 'N/A';
                    return [
                        'id' => $carpet->id,
                        'order_id' => $carpet->order_id,
                        'identifier' => '#' . $carpet->id,
                        'order' => [
                            'id' => $carpet->order->id ?? null,
                            'client' => [
                                'id' => $client->id ?? null,
                                'full_name' => $fullName
                            ]
                        ]
                    ];
                })
                ->toArray();
        } else {
            $this->carpets = $this->allUnassignedCarpets;
        }
        $this->showDropdown = true;
    }

    public function selectCarpet($carpetId)
    {
        $carpetData = collect($this->carpets)->firstWhere('id', $carpetId);
        if ($carpetData) {
            $this->selectedCarpet = $carpetData;
        } else {
            $carpet = OrderCarpet::with('order.client')->find($carpetId);
            if ($carpet) {
                $client = $carpet->order->client ?? null;
                $fullName = $client ? trim($client->first_name . ' ' . $client->last_name) : 'N/A';
                $this->selectedCarpet = [
                    'id' => $carpet->id,
                    'order_id' => $carpet->order_id,
                    'identifier' => '#' . $carpet->id,
                    'order' => [
                        'id' => $carpet->order->id ?? null,
                        'client' => [
                            'id' => $client->id ?? null,
                            'full_name' => $fullName
                        ]
                    ]
                ];
            }
        }
        $this->searchTerm = $this->selectedCarpet['identifier'] . ' - ' . ($this->selectedCarpet['order']['client']['full_name'] ?? 'N/A');
        $this->showDropdown = false;
    }

    public function assignQrCode(QrCodeService $qrCodeService)
    {
        $this->resetValidation('assignment');

        if ($this->selectedCarpet && !isset($this->selectedCarpet['qr_code'])) {
            $referenceCode = pathinfo($this->qrCodeReference, PATHINFO_FILENAME);
            $success = $qrCodeService->assignQrCodeToCarpet($referenceCode, $this->selectedCarpet['id']);

            if ($success) {
                $this->dispatch('qr-assigned');
                $this->dispatch('close-assign-modal');

                $this->dispatch('show-message', [
                    'message' => __('QR code assigned successfully.'),
                    'type' => 'success'
                ]);

                $this->reset(['searchTerm', 'carpets', 'selectedCarpet', 'qrCodeReference']);
                $this->showModal = false;

                return;
            } else {
                $this->addError('assignment', __('Failed to assign QR Code. It might already be assigned or the carpet already has one.'));

                $this->dispatch('show-message', [
                    'message' => __('Failed to assign QR Code.'),
                    'type' => 'error'
                ]);
            }
        } elseif ($this->selectedCarpet && isset($this->selectedCarpet['qr_code'])) {
            $this->addError('assignment', __('Selected carpet already has a QR Code assigned.'));

            $this->dispatch('show-message', [
                'message' => __('Selected carpet already has a QR Code assigned.'),
                'type' => 'error'
            ]);
        } else {
            $this->addError('assignment', __('No carpet selected for assignment.'));

            $this->dispatch('show-message', [
                'message' => __('No carpet selected for assignment.'),
                'type' => 'error'
            ]);
        }
    }


    public function render()
    {
        return view('livewire.qr.assign-modal');
    }
}
