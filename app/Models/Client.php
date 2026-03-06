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
        'email',
        'street_name',
        'street_number',
        'postal_code',
        'city',
        'phone_number',
        'latitude',
        'longitude',
        'remarks',
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
            $coordinates = Cache::remember($cacheKey, 604800, function () use ($address) {
                return $this->performGeocoding($address);
            });

            if ($coordinates) {
                $this->latitude = $coordinates['lat'];
                $this->longitude = $coordinates['lng'];

                Log::info('Successfully geocoded address', [
                    'client_id' => $this->id,
                    'address' => $address,
                    'coordinates' => $coordinates,
                    'used_variant' => $coordinates['used_variant'] ?? 'original'
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
     * Generate address variants for fallback geocoding
     */
    private function generateAddressVariants($address)
    {
        $variants = [$address];

        $variant1 = preg_replace('/(\d+)[A-Za-z](\/\d+)/', '$1$2', $address);
        if ($variant1 !== $address) {
            $variants[] = $variant1;
        }

        $variant2 = preg_replace('/(\d+)[A-Za-z]\b/', '$1', $address);
        if ($variant2 !== $address && !\in_array($variant2, $variants)) {
            $variants[] = $variant2;
        }

        $variant3 = preg_replace('/(\d+[A-Za-z]?)\/\d+/', '$1', $address);
        if ($variant3 !== $address && !\in_array($variant3, $variants)) {
            $variants[] = $variant3;
        }

        $variant4 = preg_replace('/(\d+)[A-Za-z]?(\/\d+)?/', '$1', $address);
        if ($variant4 !== $address && !\in_array($variant4, $variants)) {
            $variants[] = $variant4;
        }

        $variant5 = preg_replace('/\d{2}-\d{3},?\s*/', '', $address);
        if ($variant5 !== $address && !\in_array($variant5, $variants)) {
            $variants[] = $variant5;
        }

        return \array_slice($variants, 0, 5);
    }

    /**
     * Perform actual geocoding using Nominatim API with fallback variants
     */
    private function performGeocoding($address)
    {
        $variants = $this->generateAddressVariants($address);

        foreach ($variants as $index => $variant) {
            try {
                $params = [
                    'format' => 'json',
                    'q' => $variant,
                    'limit' => 1,
                    'addressdetails' => 1,
                    'countrycodes' => 'pl'
                ];

                Log::debug('[Geocoding] REQUEST', [
                    'variant_index' => $index,
                    'address' => $variant,
                    'url' => 'https://nominatim.openstreetmap.org/search?' . http_build_query($params),
                ]);

                $response = Http::timeout(10)
                    ->retry(2, 1000)
                    ->withHeaders([
                        'User-Agent' => config('app.name', 'Laravel') . ' Geocoding Service'
                    ])
                    ->get('https://nominatim.openstreetmap.org/search', $params);

                Log::debug('[Geocoding] RESPONSE', [
                    'variant_index' => $index,
                    'address' => $variant,
                    'http_status' => $response->status(),
                    'body' => $response->json(),
                ]);

                if ($response->successful()) {
                    $results = $response->json();

                    if (!empty($results) && isset($results[0]['lat'], $results[0]['lon'])) {
                        Log::info('[Geocoding] SUCCESS', [
                            'original_address' => $address,
                            'used_variant' => $variant,
                            'variant_index' => $index,
                            'lat' => $results[0]['lat'],
                            'lon' => $results[0]['lon'],
                            'display_name' => $results[0]['display_name'] ?? null,
                        ]);

                        return [
                            'lat' => (float) $results[0]['lat'],
                            'lng' => (float) $results[0]['lon'],
                            'display_name' => $results[0]['display_name'] ?? null,
                            'used_variant' => $variant,
                            'variant_index' => $index
                        ];
                    }

                    Log::warning('[Geocoding] EMPTY RESULTS', [
                        'variant_index' => $index,
                        'address' => $variant,
                    ]);
                } else {
                    Log::warning('[Geocoding] HTTP ERROR', [
                        'variant_index' => $index,
                        'address' => $variant,
                        'http_status' => $response->status(),
                    ]);
                }

                if ($index < \count($variants) - 1) {
                    sleep(1);
                }
            } catch (\Exception $e) {
                Log::error('[Geocoding] EXCEPTION', [
                    'variant_index' => $index,
                    'address' => $variant,
                    'error' => $e->getMessage(),
                ]);
                continue;
            }
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
