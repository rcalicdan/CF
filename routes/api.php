<?php

use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\Auth\AuthUserController;
use App\Http\Controllers\Api\CarpetService\PriceListController;
use App\Http\Controllers\Api\CarpetService\ServiceController;
use App\Http\Controllers\Api\CarpetService\ServicePriceListController;
use App\Http\Controllers\Api\Client\ClientController;
use App\Http\Controllers\Api\Complaints\ComplaintController;
use App\Http\Controllers\Api\Driver\DriverController;
use App\Http\Controllers\Api\Orders\OrderCarpetController;
use App\Http\Controllers\Api\Orders\OrderCarpetMeasurementController;
use App\Http\Controllers\Api\Orders\OrderCarpetPhotoController;
use App\Http\Controllers\Api\Orders\OrderCarpetQrController;
use App\Http\Controllers\Api\Orders\OrderController;
use App\Http\Controllers\Api\Orders\OrderDeliveryController;
use App\Http\Controllers\Api\Orders\OrderSearchDateController;
use App\Http\Controllers\Api\QrCode\ValidateQrController;
use App\Http\Controllers\Api\Routing\RouteDataController;
use App\Http\Controllers\Api\Sms\SendCustomSmsController;
use App\Http\Controllers\Api\User\UserController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:10,1');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:10,1');
})->withoutMiddleware(['auth:api']);

Route::middleware(['auth:api', 'throttle:120,1'])->name('api.')->group(function () {
    Route::prefix('auth')->group(function () {
        Route::get('/user/profile', [AuthUserController::class, 'index']);
        Route::post('/user/profile/update', [AuthUserController::class, 'update']);
        Route::post('/logout', [AuthController::class, 'logout']);
    });

    Route::apiResource('services', ServiceController::class);
    Route::apiResource('price-lists', PriceListController::class);
    Route::apiResource('clients', ClientController::class);
    Route::apiResource('service-price-lists', ServicePriceListController::class);
    Route::apiResource('orders', OrderController::class)->except('destroy');
    Route::apiResource('order-carpets', OrderCarpetController::class);

    Route::prefix('/order-carpets')->group(function () {
        Route::post('/{orderCarpet}/upload-photo', [OrderCarpetPhotoController::class, 'store']);
        Route::post('/{orderCarpet}/measure-carpet', [OrderCarpetMeasurementController::class, 'measureCarpet']);
        Route::post('/{orderCarpet}/assign-qr', [OrderCarpetQrController::class, 'assignQr']);
    });

    Route::get('/find-carpet-by-qr', [OrderCarpetQrController::class, 'findByQr']);
    Route::delete('carpet-photo/{carpetPhoto}', [OrderCarpetPhotoController::class, 'destroy']);
    Route::apiResource('drivers', DriverController::class)->except(['create', 'destroy']);
    Route::apiResource('users', UserController::class);

    Route::prefix('validate-qr')->group(function () {
        Route::post('/measure-validation', [ValidateQrController::class, 'measureQrValidation']);
        Route::post('/package-completed-validation', [ValidateQrController::class, 'packageCompleteQrValidation']);
        Route::post('/delivery-validation', [ValidateQrController::class, 'deliveryQrValidation']);
        Route::post('/completed-laundry-validation', [ValidateQrController::class, 'validateCompletedLaundryCarpets']);
    });

    Route::prefix('deliveries')->group(function () {
        Route::post('/{order}/confirm-delivery', [OrderDeliveryController::class, 'confirmDelivery']);
        Route::post('/{order}/mark-as-undelivered', [OrderDeliveryController::class, 'markAsUndelivered']);
    });

    Route::prefix('complaints')->group(function () {
        Route::get('', [ComplaintController::class, 'index']);
        Route::get('/{complaint}', [ComplaintController::class, 'show']);
        Route::post('/{orderCarpet}/store-complaint', [ComplaintController::class, 'store']);
        Route::put('/{complaint}', [ComplaintController::class, 'update']);
        Route::delete('/{complaint}', [ComplaintController::class, 'destroy']);
    });

    Route::prefix('route-data')->name('route-data.')->group(function () {
        Route::get('/drivers', [RouteDataController::class, 'getDrivers']);
        Route::get('/orders', [RouteDataController::class, 'getOrdersForDriverAndDate']);
        Route::get('/all-orders', [RouteDataController::class, 'getAllOrdersForDateRange']);
        Route::get('/statistics', [RouteDataController::class, 'getRouteStatistics']);
        Route::post('/geocode', [RouteDataController::class, 'triggerGeocoding']);

        Route::post('/save-optimization', [RouteDataController::class, 'saveRouteOptimization']);
        Route::get('/saved-optimization', [RouteDataController::class, 'getSavedRouteOptimization']);
    });

    Route::post('send-sms', [SendCustomSmsController::class, 'sendSms'])->middleware('throttle:15,1');
    Route::post('check-qr-exists', [OrderCarpetQrController::class, 'checkQrExists']);
    Route::get('orders-by-schedule-date', [OrderSearchDateController::class, 'getOrdersByDate']);
});
