<?php

namespace App\ActionService;

use App\Models\Client;

/**
 * Class ClientService
 *
 * Handles business logic for client management in the carpet cleaning service
 */
class ClientService
{
    public function getAllClients()
    {
        \Log::info('Request Parameters:', request()->all());

        return Client::when(request('first_name'), function ($query) {
            $query->where('first_name', 'like', '%'.request('first_name').'%');
        })
            ->when(request('last_name'), function ($query) {
                $query->where('last_name', 'like', '%'.request('last_name').'%');
            })
            ->when(request('phone_number'), function ($query) {
                $query->where('phone_number', 'like', '%'.request('phone_number').'%');
            })
            ->when(request('street_name'), function ($query) {
                $query->where('street_name', 'like', '%'.request('street_name').'%');
            })
            ->when(request('street_number'), function ($query) {
                $query->where('street_number', 'like', '%'.request('street_number').'%');
            })
            ->when(request('city'), function ($query) {
                $query->where('city', 'like', '%'.request('city').'%');
            })
            ->when(request('postal_code'), function ($query) {
                $query->where('postal_code', 'like', '%'.request('postal_code').'%');
            })
            ->paginate(30);
    }

    public function createClient(array $data)
    {
        return Client::create($data);
    }

    public function updateClient(string $clientId, array $data)
    {
        $client = Client::findOrFail($clientId);
        $client->update($data);

        return $client;
    }

    public function deleteClient(string $clientId)
    {
        $client = Client::findOrFail($clientId);
        $client->delete();

        return true;
    }

    public function showSelectedClient(string $clientId)
    {
        return Client::findOrFail($clientId);
    }
}
