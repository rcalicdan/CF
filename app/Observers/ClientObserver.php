<?php
// app/Observers/ClientObserver.php

namespace App\Observers;

use App\Models\Client;
use Illuminate\Support\Facades\Log;

class ClientObserver
{
    /**
     * Handle the Client "created" event.
     */
    public function created(Client $client): void
    {
        $this->handleGeocoding($client, 'created');
    }

    /**
     * Handle the Client "updating" event.
     */
    public function updating(Client $client): void
    {
        $addressFields = ['street_name', 'street_number', 'postal_code', 'city'];

        if ($client->isDirty($addressFields)) {
            $client->latitude = null;
            $client->longitude = null;
        }
    }

    /**
     * Handle the Client "updated" event.
     */
    public function updated(Client $client): void
    {
        $addressFields = ['street_name', 'street_number', 'postal_code', 'city'];
        $hasAddressChanges = collect($addressFields)
            ->some(fn($field) => $client->wasChanged($field));

        if ($hasAddressChanges) {
            $this->handleGeocoding($client, 'updated');
        }
    }

    /**
     * Handle the Client "deleted" event.
     */
    public function deleted(Client $client): void
    {
        $this->clearGeocodingCache($client);
    }

    /**
     * Handle geocoding in a deferred manner
     */
    private function handleGeocoding(Client $client, string $action): void
    {
        if (!$this->hasMinimumAddressInfo($client)) {
            Log::info("Skipping geocoding - insufficient address data", [
                'client_id' => $client->id,
                'action' => $action,
                'address' => $client->full_address
            ]);
            return;
        }

        defer(function () use ($client, $action) {
            $this->performDeferredGeocoding($client, $action);
        });

        Log::info("Geocoding queued for deferred processing", [
            'client_id' => $client->id,
            'action' => $action,
            'address' => $client->full_address
        ]);
    }

    /**
     * Perform the actual geocoding in the deferred context
     */
    private function performDeferredGeocoding(Client $client, string $action): void
    {
        try {
            Log::info("Starting deferred geocoding", [
                'client_id' => $client->id,
                'action' => $action,
                'address' => $client->full_address
            ]);

            $freshClient = Client::find($client->id);

            if (!$freshClient) {
                Log::warning("Client not found during deferred geocoding", [
                    'client_id' => $client->id
                ]);
                return;
            }

            if ($freshClient->hasCoordinates()) {
                Log::info("Skipping geocoding - coordinates already exist", [
                    'client_id' => $freshClient->id,
                    'lat' => $freshClient->latitude,
                    'lng' => $freshClient->longitude
                ]);
                return;
            }

            $result = $freshClient->geocodeAddress();

            if ($result) {
                $freshClient->saveQuietly();

                Log::info("Deferred geocoding completed successfully", [
                    'client_id' => $freshClient->id,
                    'action' => $action,
                    'address' => $freshClient->full_address,
                    'coordinates' => [
                        'lat' => $freshClient->latitude,
                        'lng' => $freshClient->longitude
                    ]
                ]);
            } else {
                Log::warning("Deferred geocoding failed", [
                    'client_id' => $freshClient->id,
                    'action' => $action,
                    'address' => $freshClient->full_address
                ]);
            }
        } catch (\Exception $e) {
            Log::error("Exception during deferred geocoding", [
                'client_id' => $client->id,
                'action' => $action,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Check if client has minimum required address information
     */
    private function hasMinimumAddressInfo(Client $client): bool
    {
        return !empty($client->street_name) && !empty($client->city);
    }

    /**
     * Clear geocoding cache for a client
     */
    private function clearGeocodingCache(Client $client): void
    {
        if ($client->full_address) {
            $cacheKey = 'geocode_' . md5($client->full_address);
            cache()->forget($cacheKey);

            Log::info("Cleared geocoding cache", [
                'client_id' => $client->id,
                'cache_key' => $cacheKey
            ]);
        }
    }
}
