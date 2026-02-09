<?php

namespace App\Models;

use App\ActionService\EnumTranslationService;
use App\Enums\OrderStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'assigned_driver_id',
        'schedule_date',
        'price_list_id',
        'status',
        'total_amount',
        'is_complaint',
        'user_id',
    ];

    public function casts()
    {
        return [
            'schedule_date' => 'datetime',
            'is_complaint' => 'boolean',
        ];
    }

    public function getStatusLabelAttribute(): string
    {
        return $this->status ? EnumTranslationService::translate(OrderStatus::from($this->status)) : '';
    }

    public function getCreatorFullNameAttribute()
    {
        return $this->user ? $this->user->full_name : 'N/A';
    }

    public function orderHistories()
    {
        return $this->hasMany(OrderHistory::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function driver()
    {
        return $this->belongsTo(Driver::class, 'assigned_driver_id');
    }

    public function client()
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    public function orderServices()
    {
        return $this->hasMany(OrderService::class);
    }

    public function orderCarpets()
    {
        return $this->hasMany(OrderCarpet::class);
    }

    public function priceList()
    {
        return $this->belongsTo(PriceList::class, 'price_list_id');
    }

    public function services()
    {
        return $this->belongsToMany(Service::class, 'order_service')
            ->using(OrderService::class)
            ->withPivot(['quantity', 'total_price'])
            ->withTimestamps();
    }

    public function orderDeliveryConfirmation()
    {
        return $this->hasOne(OrderDeliveryConfirmation::class);
    }

    public function orderPayment()
    {
        return $this->hasOne(OrderPayment::class);
    }

    /**
     * Get the client's full name
     */
    public function getClientNameAttribute()
    {
        return $this->client ? $this->client->full_name : 'N/A';
    }

    /**
     * Get the client's address
     */
    public function getAddressAttribute()
    {
        return $this->client ? $this->client->full_address : 'N/A';
    }

    /**
     * Get the client's coordinates [lat, lng]
     */
    public function getCoordinatesAttribute()
    {
        return $this->client ? $this->client->coordinates : null;
    }

    /**
     * Check if order has valid coordinates for mapping
     */
    public function hasCoordinates()
    {
        return $this->client && $this->client->hasCoordinates();
    }

    /**
     * Get the driver's full name (from the associated user)
     */
    public function getDriverNameAttribute()
    {
        return $this->driver && $this->driver->user ? $this->driver->user->full_name : 'N/A';
    }

    /**
     * Alternative method name for driver's full name
     */
    public function getDriverFullNameAttribute()
    {
        return $this->driver && $this->driver->user ? $this->driver->user->full_name : 'N/A';
    }

    /**
     * Get the price list name
     */
    public function getPriceListNameAttribute()
    {
        return $this->priceList ? $this->priceList->name : 'N/A';
    }

    /**
     * Scope for orders with coordinates
     */
    public function scopeWithCoordinates($query)
    {
        return $query->whereHas('client', function ($q) {
            $q->withCoordinates();
        });
    }

    /**
     * Scope for orders without coordinates
     */
    public function scopeWithoutCoordinates($query)
    {
        return $query->whereHas('client', function ($q) {
            $q->withoutCoordinates();
        });
    }
}
