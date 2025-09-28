<?php

namespace App\Livewire\LaundryServices;

use App\ActionService\CarpetService;
use App\Models\Service;
use Illuminate\Validation\Rule;
use Livewire\Component;

class UpdatePage extends Component
{
    public Service $service;
    public $name = '';
    public $base_price = '';
    public $is_area_based = false;

    protected CarpetService $carpetService;

    public function boot(CarpetService $carpetService)
    {
        $this->carpetService = $carpetService;
    }

    /**
     * Mount the component and populate the form with the service's existing data.
     */
    public function mount(Service $service)
    {
        $this->service = $service;

        $this->name = $service->name;
        $this->base_price = $service->base_price;
        $this->is_area_based = $service->is_area_based;
    }

    public function rules()
    {
        return [
            'name' => ['required', 'string', 'max:255', Rule::unique('services')->ignore($this->service->id)],
            'base_price' => 'required|numeric|min:0',
            'is_area_based' => 'required|boolean',
        ];
    }

    public function validationAttributes()
    {
        return [
            'name' => 'service name',
            'base_price' => 'base price',
            'is_area_based' => 'area based pricing',
        ];
    }

    /**
     * Update the service's information.
     */
    public function update()
    {
        $this->authorize('update', $this->service);
        $this->validate();

        $data = [
            'name' => $this->name,
            'base_price' => $this->base_price,
            'is_area_based' => $this->is_area_based,
        ];

        try {
            $this->carpetService->updateCarpetService($this->service->id, $data);

            session()->flash('success', 'Usługa została pomyślnie zaktualizowana!');

            return redirect()->route('services.index');
        } catch (\Exception $e) {
            session()->flash('error', 'Wystąpił błąd podczas aktualizacji usługi. Proszę spróbować ponownie.');
        }
    }

    public function render()
    {
        $this->authorize('update', $this->service);
        return view('livewire.laundry-services.update-page');
    }
}