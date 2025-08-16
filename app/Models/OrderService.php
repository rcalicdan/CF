<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $order_id
 * @property int $service_id
 * @property int $quantity
 * @property string $total_price
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Order $order
 * @property-read \App\Models\Service $service
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderService newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderService newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderService query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderService whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderService whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderService whereOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderService whereQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderService whereServiceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderService whereTotalPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderService whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class OrderService extends Model
{
    protected $fillable = [
        'order_id',
        'service_id',
        'total_price',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function service()
    {
        return $this->belongsTo(Service::class, 'service_id');
    }
}
