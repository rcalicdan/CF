<?php

namespace App\Livewire\Dashboard;

use App\Models\Order;
use App\Models\Driver;
use Livewire\Component;
use Livewire\Attributes\On;

class AssignDriverModal extends Component
{
    public $showModal = false;
    public $orderId = null;
    public $driverSearch = '';
    public $selectedDriverId = null;
    public $showDriversDropdown = false;

    #[On('assign-driver')]
    public function openModal($orderId)
    {
        $this->orderId = $orderId;
        $this->showModal = true;
        $this->reset(['driverSearch', 'selectedDriverId', 'showDriversDropdown']);
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->reset(['orderId', 'driverSearch', 'selectedDriverId', 'showDriversDropdown']);
    }

    public function updatedDriverSearch()
    {
        $this->showDriversDropdown = !empty($this->driverSearch);
        if (empty($this->driverSearch)) {
            $this->selectedDriverId = null;
        }
    }

    public function selectDriver($driverId, $driverName)
    {
        $this->selectedDriverId = $driverId;
        $this->driverSearch = $driverName;
        $this->showDriversDropdown = false;
    }

    public function showAllDrivers()
    {
        $this->showDriversDropdown = true;
        $this->driverSearch = '';
    }

    public function hideDriversDropdown()
    {
        $this->showDriversDropdown = false;
        if (!$this->selectedDriverId) {
            $this->driverSearch = '';
        }
    }

    public function getFilteredDrivers()
    {
        $query = Driver::with('user:id,first_name,last_name')
            ->active();

        if (!empty($this->driverSearch)) {
            $query->whereHas('user', function ($q) {
                $q->where('first_name', 'like', '%' . $this->driverSearch . '%')
                    ->orWhere('last_name', 'like', '%' . $this->driverSearch . '%')
                    ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ['%' . $this->driverSearch . '%']);
            });
        }

        return $query->limit(10)->get();
    }

    public function assignDriver()
    {
        $this->validate([
            'selectedDriverId' => 'required|exists:drivers,id',
        ], [
            'selectedDriverId.required' => 'Wybierz kierowcę z listy.',
            'selectedDriverId.exists' => 'Wybrany kierowca nie istnieje w systemie.',
        ]);

        try {
            $order = Order::findOrFail($this->orderId);
            $order->update([
                'assigned_driver_id' => $this->selectedDriverId,
            ]);

            $this->dispatch('driver-assigned', orderId: $this->orderId);
            $this->dispatch('notify', [
                'type' => 'success',
                'message' => 'Kierowca został pomyślnie przypisany do zamówienia!'
            ]);

            $this->closeModal();
        } catch (\Exception $e) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Wystąpił błąd podczas przypisywania kierowcy. Spróbuj ponownie.'
            ]);
        }
    }

    public function render()
    {
        return view('livewire.dashboard.assign-driver-modal', [
            'filteredDrivers' => $this->getFilteredDrivers(),
        ]);
    }
}
