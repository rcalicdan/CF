<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $order_carpet_id
 * @property int $user_id
 * @property string $photo_path
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\OrderCarpet $orderCarpet
 * @property-read \App\Models\User $user
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderCarpetPhoto newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderCarpetPhoto newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderCarpetPhoto query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderCarpetPhoto whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderCarpetPhoto whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderCarpetPhoto whereOrderCarpetId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderCarpetPhoto wherePhotoPath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderCarpetPhoto whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderCarpetPhoto whereUserId($value)
 *
 * @mixin \Eloquent
 */
class OrderCarpetPhoto extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_carpet_id',
        'user_id',
        'photo_path',
    ];

    public function orderCarpet()
    {
        return $this->belongsTo(OrderCarpet::class, 'order_carpet_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
