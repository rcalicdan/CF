<?php

namespace App\Livewire\PriceLists;

use App\ActionService\PriceListService;
use App\Models\PriceList;
use Livewire\Component;

class ViewPage extends Component
{
    public PriceList $priceList;
    public $showMobileView = false;
    public $searchTerm = '';
    public $sortField = 'name';
    public $sortDirection = 'asc';

    protected PriceListService $priceListService;

    public function boot(PriceListService $priceListService)
    {
        $this->priceListService = $priceListService;
    }

    public function mount(PriceList $priceList)
    {
        $this->authorize('view', $priceList);
        $this->priceList = $priceList->load('services');
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function getFilteredServicesProperty()
    {
        $services = $this->priceList->services;

        if ($this->searchTerm) {
            $services = $services->filter(function ($service) {
                return stripos($service->name, $this->searchTerm) !== false;
            });
        }

        return $services->sortBy($this->sortField, SORT_REGULAR, $this->sortDirection === 'desc');
    }

    public function getTotalServicesProperty()
    {
        return $this->priceList->services->count();
    }

    public function getTotalValueProperty()
    {
        return $this->priceList->services->sum('pivot.price');
    }

    public function getAveragePriceProperty()
    {
        $services = $this->priceList->services;
        return $services->count() > 0 ? $services->avg('pivot.price') : 0;
    }

    public function getPriceDifferenceStatsProperty()
    {
        $services = $this->priceList->services;
        $totalDifference = 0;
        $count = 0;

        foreach ($services as $service) {
            $difference = $service->pivot->price - $service->base_price;
            $totalDifference += $difference;
            $count++;
        }

        return [
            'total_difference' => $totalDifference,
            'average_difference' => $count > 0 ? $totalDifference / $count : 0,
            'percentage' => $count > 0 && $services->sum('base_price') > 0
                ? ($totalDifference / $services->sum('base_price')) * 100
                : 0
        ];
    }

    public function render()
    {
        return view('livewire.price-lists.view-page');
    }
}
