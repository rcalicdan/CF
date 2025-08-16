<?php

// @formatter:off
// phpcs:ignoreFile
/**
 * A helper file for your Eloquent Models
 * Copy the phpDocs from this file to the correct Model,
 * And remove them from this file, to prevent double declarations.
 *
 * @author Barry vd. Heuvel <barryvdh@gmail.com>
 */


namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property string $first_name
 * @property string $last_name
 * @property string|null $street_name
 * @property string|null $street_number
 * @property string|null $postal_code
 * @property string|null $city
 * @property string $phone_number
 * @property string|null $remarks
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read mixed $full_address
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Order> $orders
 * @property-read int|null $orders_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client whereCity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client whereFirstName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client whereLastName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client wherePhoneNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client wherePostalCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client whereRemarks($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client whereStreetName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client whereStreetNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client whereUpdatedAt($value)
 */
	class Client extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property int $order_carpet_id
 * @property string $complaint_details
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\OrderCarpet $orderCarpet
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Complaint newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Complaint newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Complaint query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Complaint whereComplaintDetails($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Complaint whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Complaint whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Complaint whereOrderCarpetId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Complaint whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Complaint whereUpdatedAt($value)
 */
	class Complaint extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property int $user_id
 * @property string|null $license_number
 * @property string|null $vehicle_details
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Order> $orders
 * @property-read int|null $orders_count
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Driver newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Driver newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Driver query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Driver whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Driver whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Driver whereLicenseNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Driver whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Driver whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Driver whereVehicleDetails($value)
 */
	class Driver extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property int $order_id
 * @property string|null $qr_code
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $remarks
 * @property-read \App\Models\Complaint|null $complaint
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\OrderCarpetPhoto> $orderCarpetPhotos
 * @property-read int|null $order_carpet_photos_count
 * @property-read \App\Models\Order $orders
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderCarpet newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderCarpet newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderCarpet query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderCarpet whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderCarpet whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderCarpet whereOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderCarpet whereQrCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderCarpet whereRemarks($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderCarpet whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderCarpet whereUpdatedAt($value)
 */
	class OrderCarpet extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property int $order_carpet_id
 * @property int $user_id
 * @property string $photo_path
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\OrderCarpet $orderCarpet
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderCarpetPhoto newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderCarpetPhoto newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderCarpetPhoto query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderCarpetPhoto whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderCarpetPhoto whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderCarpetPhoto whereOrderCarpetId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderCarpetPhoto wherePhotoPath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderCarpetPhoto whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderCarpetPhoto whereUserId($value)
 */
	class OrderCarpetPhoto extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property int $order_id
 * @property string $confirmation_type
 * @property string|null $signature_url
 * @property string|null $data
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Order $order
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderDeliveryConfirmation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderDeliveryConfirmation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderDeliveryConfirmation query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderDeliveryConfirmation whereConfirmationType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderDeliveryConfirmation whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderDeliveryConfirmation whereData($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderDeliveryConfirmation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderDeliveryConfirmation whereOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderDeliveryConfirmation whereSignatureUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderDeliveryConfirmation whereUpdatedAt($value)
 */
	class OrderDeliveryConfirmation extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property int $order_id
 * @property int $service_id
 * @property int $quantity
 * @property string $total_price
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Order $order
 * @property-read \App\Models\Service $service
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
 */
	class OrderService extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property int $price_list_id
 * @property int $service_id
 * @property string $price
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\PriceList $priceList
 * @property-read \App\Models\Service $service
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ServicePriceList newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ServicePriceList newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ServicePriceList query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ServicePriceList whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ServicePriceList whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ServicePriceList wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ServicePriceList wherePriceListId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ServicePriceList whereServiceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ServicePriceList whereUpdatedAt($value)
 */
	class ServicePriceList extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property string $first_name
 * @property string $last_name
 * @property string $email
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 * @property string $password
 * @property string|null $profile_path
 * @property string $role
 * @property string|null $remember_token
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Laravel\Passport\Client> $clients
 * @property-read int|null $clients_count
 * @property-read \App\Models\Driver|null $driver
 * @property-read mixed $full_name
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\OrderCarpetPhoto> $orderCarpetPhotos
 * @property-read int|null $order_carpet_photos_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Order> $orders
 * @property-read int|null $orders_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Laravel\Passport\Token> $tokens
 * @property-read int|null $tokens_count
 * @method static \Database\Factories\UserFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmailVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereFirstName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereLastName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereProfilePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereRole($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereUpdatedAt($value)
 */
	class User extends \Eloquent {}
}

