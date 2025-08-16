<?php

namespace App\ActionService;

use App\Models\Service;

class CarpetService
{
    public function getAllCarpetServices()
    {
        return Service::when(request('name'), function ($query) {
            $query->where('name', 'like', '%'.request('name').'%');
        })
            ->when(request('base_price_min'), function ($query) {
                $query->where('base_price', '>=', request('base_price_min'));
            })
            ->when(request('base_price_max'), function ($query) {
                $query->where('base_price', '<=', request('base_price_max'));
            })
            ->when(request('is_area_based'), function ($query) {
                $query->where('is_area_based', request('is_area_based'));
            })
            ->paginate(10);
    }

    public function showSelectedCarpetService(string $carpetServiceId)
    {
        return Service::findOrFail($carpetServiceId);
    }

    public function createCarpetService(array $data)
    {
        return Service::create($data);
    }

    public function updateCarpetService(string $carpetServiceId, array $data)
    {
        $carpetService = Service::findOrFail($carpetServiceId);
        $carpetService->update($data);

        return $carpetService;
    }

    public function deleteCarpetService(string $carpetServiceId)
    {
        $carpetService = Service::findOrFail($carpetServiceId);
        $carpetService->delete();

        return true;
    }
}
