<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class Client extends Model
{
    use HasFactory;

    protected $fillable = [
        'first_name',
        'last_name',
        'street_name',
        'street_number',
        'postal_code',
        'city',
        'phone_number',
        'latitude',
        'longitude',
    ];

    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
    ];

    /**
     * Get the full address string
     */
    public function getFullAddressAttribute()
    {
        $parts = array_filter([
            $this->street_name,
            $this->street_number,
            $this->postal_code,
            $this->city
        ]);

        return implode(', ', $parts);
    }

    /**
     * Get the full name
     */
    public function getFullNameAttribute()
    {
        return "{$this->first_name} {$this->last_name}";
    }

    /**
     * Get coordinates as array [lat, lng] - keeping this format for frontend consistency
     */
    public function getCoordinatesAttribute()
    {
        if ($this->latitude && $this->longitude) {
            return [(float) $this->latitude, (float) $this->longitude];
        }
        return null;
    }

    /**
     * Get coordinates in VROOM format [lng, lat] 
     */
    public function getVroomCoordinatesAttribute()
    {
        if ($this->latitude && $this->longitude) {
            return [(float) $this->longitude, (float) $this->latitude];
        }
        return null;
    }

    /**
     * Geocode the client's address and update coordinates
     */
    public function geocodeAddress()
    {
        $address = $this->full_address;

        if (empty($address)) {
            Log::warning('Cannot geocode empty address for client', ['client_id' => $this->id]);
            return false;
        }

        $cacheKey = 'geocode_' . md5($address);

        try {
            // Check cache first (cache for 7 days)
            $coordinates = Cache::remember($cacheKey, 604800, function () use ($address) {
                return $this->performGeocoding($address);
            });

            if ($coordinates) {
                $this->latitude = $coordinates['lat'];
                $this->longitude = $coordinates['lng'];

                Log::info('Successfully geocoded address', [
                    'client_id' => $this->id,
                    'address' => $address,
                    'coordinates' => $coordinates
                ]);

                return true;
            }
        } catch (\Exception $e) {
            Log::error('Geocoding failed for client', [
                'client_id' => $this->id,
                'address' => $address,
                'error' => $e->getMessage()
            ]);
        }

        return false;
    }

    /**
     * Perform actual geocoding using Nominatim API
     */
    private function performGeocoding($address)
    {
        try {
            $response = Http::timeout(10)
                ->retry(3, 1000)
                ->withHeaders([
                    'User-Agent' => config('app.name', 'Laravel') . ' Geocoding Service'
                ])
                ->get('https://nominatim.openstreetmap.org/search', [
                    'format' => 'json',
                    'q' => $address,
                    'limit' => 1,
                    'addressdetails' => 1,
                    'countrycodes' => 'pl'
                ]);

            if ($response->successful()) {
                $results = $response->json();

                if (!empty($results) && isset($results[0]['lat'], $results[0]['lon'])) {
                    return [
                        'lat' => (float) $results[0]['lat'],
                        'lng' => (float) $results[0]['lon'],
                        'display_name' => $results[0]['display_name'] ?? null
                    ];
                }
            }
        } catch (\Exception $e) {
            throw $e;
        }

        return null;
    }

    /**
     * Manually trigger geocoding (useful for existing records)
     */
    public function forceGeocode()
    {
        $address = $this->full_address;
        $cacheKey = 'geocode_' . md5($address);

        Cache::forget($cacheKey);
        return $this->geocodeAddress();
    }

    /**
     * Check if client has valid coordinates
     */
    public function hasCoordinates()
    {
        return !is_null($this->latitude) && !is_null($this->longitude)
            && $this->latitude != 0 && $this->longitude != 0;
    }

    /**
     * Scope for clients with coordinates
     */
    public function scopeWithCoordinates($query)
    {
        return $query->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->where('latitude', '!=', 0)
            ->where('longitude', '!=', 0);
    }

    /**
     * Scope for clients without coordinates
     */
    public function scopeWithoutCoordinates($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('latitude')
                ->orWhereNull('longitude')
                ->orWhere('latitude', 0)
                ->orWhere('longitude', 0);
        });
    }

    /**
     * Relationship with orders
     */
    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}
