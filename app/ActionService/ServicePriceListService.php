<?php

namespace App\ActionService;

use App\Models\ServicePriceList;

class ServicePriceListService
{
    public function getAllServicePriceLists()
    {
        $data = ServicePriceList::with(['priceList', 'service', 'priceList.services'])
            ->when(request()->input('id'), function ($query) {
                $query->where('id', request()->input('id'));
            })
            ->when(request()->input('service_id'), function ($query) {
                $query->where('service_id', request()->input('service_id'));
            })
            ->when(request()->input('service_name'), function ($query) {
                $query->whereHas('service', function ($subquery) {
                    $subquery->where('name', 'like', '%'.request()->input('service_name').'%');
                });
            })
            ->when(request()->input('price_list_id'), function ($query) {
                $query->where('price_list_id', request()->input('price_list_id'));
            })
            ->when(request()->input('price'), function ($query) {
                $query->where('price', request()->input('price'));
            })
            ->when(request()->input('price_list_name'), function ($query) {
                $query->whereHas('priceList', function ($subquery) {
                    $subquery->where('name', 'like', '%'.request()->input('price_list_name').'%');
                });
            })
            ->when(request()->input('price'), function ($query) {
                $query->where('price', request()->input('price'));
            })
            ->paginate(20);

        return $data;
    }

    public function showSelectedPriceList(string $servicePriceListId)
    {
        return ServicePriceList::with(['priceList', 'service', 'priceList.services'])->findOrFail($servicePriceListId);
    }

    public function createPriceListService(array $data)
    {
        $servicePriceList = ServicePriceList::create($data);

        return $servicePriceList;
    }

    public function updatePriceListService(string $servicePriceListId, array $data)
    {
        $servicePriceList = ServicePriceList::findOrFail($servicePriceListId);
        $servicePriceList->update($data);

        return $servicePriceList;
    }

    public function deletePriceListService(string $servicePriceListId)
    {
        $servicePriceList = ServicePriceList::findOrFail($servicePriceListId);
        $servicePriceList->delete();

        return true;
    }
}
