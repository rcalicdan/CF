<?php

namespace App\ActionService;

use App\Models\Driver;

class DriverService
{
    /**
     * Get a paginated list of drivers.
     * By default, it returns only active drivers (where the related user is active).
     * This can be overridden by passing the 'active' query parameter.
     * e.g., ?active=false (for inactive) or ?active=all (for all drivers).
     */
    public function getAllDrivers()
    {
        $query = Driver::with('user')
            ->whereHas('user', function ($query) {
            $query->whereNotNull('first_name')
                  ->where('first_name', '!=', '')
                  ->whereNotNull('last_name')
                  ->where('last_name', '!=', '');
            })
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
            });


        if (request()->has('active')) {
            if (request('active') !== 'all') {
                $status = filter_var(request('active'), FILTER_VALIDATE_BOOLEAN);
                $query->whereHas('user', function ($q) use ($status) {
                    $q->where('active', $status);
                });
            }
        } else {
            $query->whereHas('user', function ($q) {
                $q->where('active', true);
            });
        }

        return $query->paginate(30);
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
