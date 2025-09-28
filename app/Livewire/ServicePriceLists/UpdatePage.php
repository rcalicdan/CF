<?php

namespace App\Livewire\ServicePriceLists;

use App\ActionService\ServicePriceListService;
use App\Models\PriceList;
use App\Models\Service;
use App\Models\ServicePriceList;
use Illuminate\Validation\Rule;
use Livewire\Component;

class UpdatePage extends Component
{
    public ServicePriceList $servicePriceList;
    public $price_list_id = '';
    public $service_id = '';
    public $price = '';

    protected ServicePriceListService $servicePriceListService;

    public function boot(ServicePriceListService $servicePriceListService)
    {
        $this->servicePriceListService = $servicePriceListService;
    }

    public function mount(ServicePriceList $servicePriceList)
    {
        $this->servicePriceList = $servicePriceList;
        $this->price_list_id = $servicePriceList->price_list_id;
        $this->service_id = $servicePriceList->service_id;
        $this->price = $servicePriceList->price;
    }

    public function rules()
    {
        return [
            'price_list_id' => 'required|exists:price_lists,id',
            'service_id' => [
                'required',
                'exists:services,id',
                Rule::unique('service_price_lists')
                    ->where('price_list_id', $this->price_list_id)
                    ->ignore($this->servicePriceList->id)
            ],
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

    public function update()
    {
        $this->authorize('update', $this->servicePriceList);
        $this->validate();

        $data = [
            'price_list_id' => $this->price_list_id,
            'service_id' => $this->service_id,
            'price' => $this->price,
        ];

        try {
            $this->servicePriceListService->updatePriceListService($this->servicePriceList->id, $data);

            session()->flash('success', 'Cena usługi została pomyślnie zaktualizowana!');

            return redirect()->route('service-price-lists.index');
        } catch (\Exception $e) {
            session()->flash('error', 'Wystąpił błąd podczas aktualizacji ceny usługi. Proszę spróbować ponownie.');
        }
    }

    public function render()
    {
        $this->authorize('update', $this->servicePriceList);
        $priceLists = PriceList::orderBy('name')->get()->mapWithKeys(fn($priceList) => [
            $priceList->id => $priceList->name . ' (' . $priceList->location_postal_code . ')'
        ]);

        $services = Service::orderBy('name')->get()->mapWithKeys(fn($service) => [
            $service->id => $service->name . ' (Base: $' . number_format($service->base_price, 2) . ')'
        ]);

        return view('livewire.service-price-lists.update-page', [
            'priceListOptions' => $priceLists,
            'serviceOptions' => $services
        ]);
    }
}
