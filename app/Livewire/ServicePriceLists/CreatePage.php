<?php

namespace App\Livewire\ServicePriceLists;

use App\ActionService\ServicePriceListService;
use App\Models\PriceList;
use App\Models\Service;
use App\Models\ServicePriceList;
use Livewire\Component;

class CreatePage extends Component
{
    public $price_list_id = '';
    public $service_id = '';
    public $price = '';

    protected ServicePriceListService $servicePriceListService;

    public function boot(ServicePriceListService $servicePriceListService)
    {
        $this->servicePriceListService = $servicePriceListService;
    }

    public function rules()
    {
        return [
            'price_list_id' => 'required|exists:price_lists,id',
            'service_id' => 'required|exists:services,id',
            'price' => 'required|numeric|min:0|max:999999.99',
        ];
    }

    public function validationAttributes()
    {
        return [
            'price_list_id' => 'price list',
            'service_id' => 'service',
            'price' => 'price',
        ];
    }

    public function save()
    {
        $this->authorize('create', ServicePriceList::class);
        $this->validate();

        // Check if this combination already exists
        $exists = ServicePriceList::where('price_list_id', $this->price_list_id)
            ->where('service_id', $this->service_id)
            ->exists();

        if ($exists) {
            $this->addError('service_id', 'Ta usługa ma już ustaloną cenę dla wybranego cennika.');
            return;
        }

        $data = [
            'price_list_id' => $this->price_list_id,
            'service_id' => $this->service_id,
            'price' => $this->price,
        ];

        try {
            $this->servicePriceListService->createPriceListService($data);

            session()->flash('success', 'Cena usługi została pomyślnie dodana!');

            return redirect()->route('service-price-lists.index');
        } catch (\Exception $e) {
            session()->flash('error', 'Wystąpił błąd podczas dodawania ceny usługi. Proszę spróbować ponownie.');
        }
    }

    public function render()
    {
        $this->authorize('create', ServicePriceList::class);
        $priceLists = PriceList::orderBy('name')->get()->mapWithKeys(fn($priceList) => [
            $priceList->id => $priceList->name . ' (' . $priceList->location_postal_code . ')'
        ]);

        $services = Service::orderBy('name')->get()->mapWithKeys(fn($service) => [
            $service->id => $service->name . ' (' . __('Base') . ': ' . number_format($service->base_price, 2) . ')'
        ]);

        return view('livewire.service-price-lists.create-page', [
            'priceListOptions' => $priceLists,
            'serviceOptions' => $services
        ]);
    }
}
