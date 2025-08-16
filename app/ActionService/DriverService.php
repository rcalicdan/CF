<?php

namespace App\ActionService;

use App\Models\Driver;

class DriverService
{
    public function getAllDrivers()
    {
        return Driver::with('user')
            ->when(request('license_number'), function ($query) {
                $query->where('license_number', 'like', '%'.request('license_number').'%');
            })
            ->when(request('vehicle_details'), function ($query) {
                $query->where('vehicle_details', 'like', '%'.request('vehicle_details').'%');
            })
            ->when(request('first_name'), function ($query) {
                $query->whereHas('user', function ($subQuery) {
                    $subQuery->where('first_name', 'like', '%'.request('first_name').'%');
                });
            })
            ->when(request('last_name'), function ($query) {
                $query->whereHas('user', function ($subQuery) {
                    $subQuery->where('last_name', 'like', '%'.request('last_name').'%');
                });
            })
            ->when(request('email'), function ($query) {
                $query->whereHas('user', function ($subQuery) {
                    $subQuery->where('email', 'like', '%'.request('email').'%');
                });
            })
            ->paginate(30);
    }

    public function getDriverInformation(Driver $driver)
    {
        return $driver->load('user');
    }

    public function updateDriverInformation(Driver $driver, array $data)
    {
        $driver->update($data);

        return $driver->load('user');
    }
}
