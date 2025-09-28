<?php

namespace App\Livewire\LaundryServices;

use App\ActionService\CarpetService;
use App\Models\Service;
use Livewire\Component;

class CreatePage extends Component
{
    public $name = '';
    public $base_price = '';
    public $is_area_based = false;

    protected CarpetService $carpetService;

    public function boot(CarpetService $carpetService)
    {
        $this->carpetService = $carpetService;
    }

    public function mount()
    {
        $this->is_area_based = false;
    }

    public function rules()
    {
        return [
            'name' => 'required|string|max:255|unique:services,name',
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

    public function save()
    {
        $this->authorize('create', Service::class);
        $this->validate();

        $data = [
            'name' => $this->name,
            'base_price' => $this->base_price,
            'is_area_based' => $this->is_area_based,
        ];

        try {
           $this->carpetService->createCarpetService($data);

            session()->flash('success', 'Usługa została pomyślnie utworzona!');

            return redirect()->route('services.index');
        } catch (\Exception $e) {
            session()->flash('error', 'Wystąpił błąd podczas tworzenia usługi. Proszę spróbować ponownie.');
        }
    }

    public function render()
    {
        $this->authorize('create', Service::class);
        return view('livewire.laundry-services.create-page');
    }
}