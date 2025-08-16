<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * @property int $id
 * @property int $price_list_id
 * @property int $service_id
 * @property string $price
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\PriceList $priceList
 * @property-read \App\Models\Service $service
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ServicePriceList newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ServicePriceList newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ServicePriceList query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ServicePriceList whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ServicePriceList whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ServicePriceList wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ServicePriceList wherePriceListId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ServicePriceList whereServiceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ServicePriceList whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class ServicePriceList extends Pivot
{
    use HasFactory;

    protected $table = 'service_price_lists';

    protected $primaryKey = 'id';

    public $incrementing = true;

    protected $fillable = [
        'price_list_id',
        'service_id',
        'price',
    ];

    public function priceList()
    {
        return $this->belongsTo(PriceList::class, 'price_list_id');
    }

    public function service()
    {
        return $this->belongsTo(Service::class, 'service_id');
    }

    public function getPriceListNameAttribute()
    {
        return $this->priceList ? $this->priceList->name . ' (' . $this->priceList->location_postal_code . ')' : 'N/A';
    }

    public function getServiceNameAttribute()
    {
        return $this->service ? $this->service->name : 'N/A';
    }

    public function getPriceComparisonAttribute()
    {
        if (!$this->service) {
            return ['text' => 'N/A', 'class' => 'bg-gray-100 text-gray-800'];
        }

        $basePrice = $this->service->base_price;
        $currentPrice = $this->price;

        if ($currentPrice > $basePrice) {
            $percentage = round((($currentPrice - $basePrice) / $basePrice) * 100, 1);
            return [
                'text' => "+{$percentage}%",
                'class' => 'bg-red-100 text-red-800'
            ];
        } elseif ($currentPrice < $basePrice) {
            $percentage = round((($basePrice - $currentPrice) / $basePrice) * 100, 1);
            return [
                'text' => "-{$percentage}%",
                'class' => 'bg-green-100 text-green-800'
            ];
        } else {
            return [
                'text' => __('Same Price'),
                'class' => 'bg-blue-100 text-blue-800'
            ];
        }
    }
}
