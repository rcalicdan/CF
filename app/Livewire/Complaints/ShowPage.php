<?php

namespace App\Livewire\Complaints;

use App\Enums\ComplaintStatus;
use App\Models\Complaint;
use Livewire\Component;

class ShowPage extends Component
{
    public Complaint $complaint;
    public $isEditingStatus = false;
    public $newStatus;

    public function mount(Complaint $complaint)
    {
        $this->authorize('view', $complaint);
        $this->complaint = $complaint->load(['orderCarpet.order.client', 'orderCarpet.services']);
        $this->newStatus = $this->complaint->status;
    }

    public function toggleStatusEdit()
    {
        $this->isEditingStatus = !$this->isEditingStatus;
        if (!$this->isEditingStatus) {
            $this->newStatus = $this->complaint->status;
        }
    }

    public function updateStatus()
    {
        $this->authorize('update', $this->complaint);
        
        $this->validate([
            'newStatus' => 'required|string|in:' . implode(',', array_column(ComplaintStatus::cases(), 'value'))
        ]);

        $this->complaint->update(['status' => $this->newStatus]);
        $this->complaint->refresh();
        $this->isEditingStatus = false;

        $this->dispatch('show-message', [
            'message' => __('Complaint status updated successfully.'),
            'type' => 'success'
        ]);
    }

    public function getStatusOptions()
    {
        return collect(ComplaintStatus::cases())->map(function ($status) {
            return [
                'value' => $status->value,
                'label' => ucfirst(str_replace('_', ' ', $status->value))
            ];
        });
    }

    public function render()
    {
        return view('livewire.complaints.show-page', [
            'statusOptions' => $this->getStatusOptions()
        ]);
    }
}