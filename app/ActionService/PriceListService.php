<?php

namespace App\ActionService;

use App\Models\PriceList;
use App\Models\ServicePriceList;
use Illuminate\Support\Facades\DB;

class PriceListService
{
    public function getAllPriceLists()
    {
        return PriceList::when(request('name'), function ($query) {
            $query->where('name', 'like', '%' . request('name') . '%');
        })
            ->when(request('location_postal_code'), function ($query) {
                $query->where('location_postal_code', 'like', '%' . request('location_postal_code') . '%');
            })
            ->paginate(30);
    }

    public function getAllServicePriceLists()
    {
        $data = PriceList::with('services')
            ->when(request('id'), function ($query) {
                $query->where('id', request('id'));
            })
            ->when(request('name'), function ($query) {
                $query->where('name', 'like', '%' . request('name') . '%');
            })
            ->when(request('location_postal_code'), function ($query) {
                $query->where('location_postal_code', 'like', '%' . request('location_postal_code') . '%');
            })
            ->paginate(30);

        return $data;
    }

    public function showSelectedPriceList(string $priceListId)
    {
        return PriceList::with('services')->findOrFail($priceListId);
    }

    public function createPriceList(array $data)
    {
        $priceList = PriceList::create($data);
        return $priceList;
    }

    public function createPriceListWithServices(array $data, array $servicePrices = [])
    {
        return DB::transaction(function () use ($data, $servicePrices) {
            $priceList = PriceList::create($data);

            if (!empty($servicePrices)) {
                $this->syncServicePrices($priceList, $servicePrices);
            }

            return $priceList;
        });
    }

    public function updatePriceList(string $priceListId, array $data)
    {
        $priceList = PriceList::findOrFail($priceListId);
        $priceList->update($data);
        return $priceList;
    }

    public function updatePriceListWithServices(string $priceListId, array $data, array $servicePrices = [])
    {
        return DB::transaction(function () use ($priceListId, $data, $servicePrices) {
            $priceList = PriceList::findOrFail($priceListId);
            $priceList->update($data);

            $this->syncServicePrices($priceList, $servicePrices);

            return $priceList;
        });
    }

    public function deletePriceList(string $priceListId)
    {
        $priceList = PriceList::findOrFail($priceListId);
        $priceList->delete();
        return true;
    }

    public function addServicePrice(string $priceListId, int $serviceId, float $price)
    {
        $priceList = PriceList::findOrFail($priceListId);

        $existingServicePrice = ServicePriceList::where('price_list_id', $priceListId)
            ->where('service_id', $serviceId)
            ->first();

        if ($existingServicePrice) {
            throw new \Exception('Service already exists in this price list.');
        }

        return ServicePriceList::create([
            'price_list_id' => $priceListId,
            'service_id' => $serviceId,
            'price' => $price,
        ]);
    }

    public function updateServicePrice(string $servicePriceListId, float $price)
    {
        $servicePriceList = ServicePriceList::findOrFail($servicePriceListId);
        $servicePriceList->update(['price' => $price]);
        return $servicePriceList;
    }


    public function removeServicePrice(string $servicePriceListId)
    {
        $servicePriceList = ServicePriceList::findOrFail($servicePriceListId);
        $servicePriceList->delete();
        return true;
    }

    private function syncServicePrices(PriceList $priceList, array $servicePrices)
    {
        $existingServicePrices = ServicePriceList::where('price_list_id', $priceList->id)
            ->get()
            ->keyBy('service_id');

        $currentServiceIds = collect($servicePrices)->pluck('service_id')->toArray();

        $servicesToRemove = $existingServicePrices->keys()->diff($currentServiceIds);
        if ($servicesToRemove->isNotEmpty()) {
            ServicePriceList::where('price_list_id', $priceList->id)
                ->whereIn('service_id', $servicesToRemove->toArray())
                ->delete();
        }

        foreach ($servicePrices as $servicePrice) {
            $existingServicePrice = $existingServicePrices->get($servicePrice['service_id']);

            if ($existingServicePrice) {
                $existingServicePrice->update([
                    'price' => $servicePrice['price']
                ]);
            } else {
                ServicePriceList::create([
                    'price_list_id' => $priceList->id,
                    'service_id' => $servicePrice['service_id'],
                    'price' => $servicePrice['price'],
                ]);
            }
        }
    }

    public function getServicePricesForPriceList(string $priceListId)
    {
        return ServicePriceList::with(['service', 'priceList'])
            ->where('price_list_id', $priceListId)
            ->get();
    }

    public function bulkUpdateServicePrices(string $priceListId, array $servicePriceUpdates)
    {
        return DB::transaction(function () use ($priceListId, $servicePriceUpdates) {
            foreach ($servicePriceUpdates as $update) {
                if (isset($update['id'])) {
                    ServicePriceList::where('id', $update['id'])
                        ->where('price_list_id', $priceListId)
                        ->update(['price' => $update['price']]);
                }
            }
        });
    }

    public function copyServicePrices(string $sourcePriceListId, string $targetPriceListId)
    {
        $sourceServicePrices = ServicePriceList::where('price_list_id', $sourcePriceListId)->get();

        return DB::transaction(function () use ($sourceServicePrices, $targetPriceListId) {
            foreach ($sourceServicePrices as $servicePrice) {
                $existingServicePrice = ServicePriceList::where('price_list_id', $targetPriceListId)
                    ->where('service_id', $servicePrice->service_id)
                    ->first();

                if (!$existingServicePrice) {
                    ServicePriceList::create([
                        'price_list_id' => $targetPriceListId,
                        'service_id' => $servicePrice->service_id,
                        'price' => $servicePrice->price,
                    ]);
                }
            }
        });
    }
}
