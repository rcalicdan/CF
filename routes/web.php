<?php

use App\Livewire\Auth\Login;
use App\Livewire\Users\CreatePage;
use App\Livewire\Users\UpdatePage;
use App\Livewire\Users\Table as UserTable;
use Illuminate\Support\Facades\Route;

Route::get('login', Login::class)->name('login');

Route::view('/', 'contents.dashboard.index')->name('dashboard')->middleware('auth');

Route::prefix('users')->middleware('auth')->name('users.')->group(function () {
    Route::get('', UserTable::class)->name('index');
    Route::get('create', CreatePage::class)->name('create');
    Route::get('{user}/edit', UpdatePage::class)->name('edit');
});

Route::prefix('clients')->middleware('auth')->name('clients.')->group(function () {
    Route::get('', App\Livewire\Clients\Table::class)->name('index');
    Route::get('create', App\Livewire\Clients\CreatePage::class)->name('create');
    Route::get('{client}/edit', App\Livewire\Clients\UpdatePage::class)->name('edit');
});

Route::get('orders/by-driver', App\Livewire\Orders\DriverTable::class)->name('by-driver');

Route::prefix('orders')->middleware('auth')->name('orders.')->group(function () {
    Route::get('', App\Livewire\Orders\Table::class)->name('index');
    Route::get('create', App\Livewire\Orders\CreatePage::class)->name('create');
    Route::get('{order}/edit', App\Livewire\Orders\UpdatePage::class)->name('edit');
    Route::get('{order}', App\Livewire\Orders\ShowPage::class)->name('show');
    Route::get('{order}/carpets/create', App\Livewire\Orders\Carpets\CarpetCreatePage::class)->name('carpets.create');
});

Route::prefix('order-carpets')->middleware('auth')->name('order-carpets.')->group(function () {
    Route::get('{carpet}/edit', App\Livewire\Orders\Carpets\CarpetUpdatePage::class)->name('edit');
    Route::get('{carpet}', \App\Livewire\Orders\Carpets\ShowPage::class)->name('show');
});


Route::prefix('price-lists')->middleware('auth')->name('price-lists.')->group(function () {
    Route::get('', App\Livewire\PriceLists\Table::class)->name('index');
    Route::get('create', App\Livewire\PriceLists\CreatePage::class)->name('create');
    Route::get('{priceList}', App\Livewire\PriceLists\ViewPage::class)->name('view');
    Route::get('{priceList}/edit', App\Livewire\PriceLists\UpdatePage::class)->name('edit');
});

Route::prefix('services')->middleware('auth')->name('services.')->group(function () {
    Route::get('', App\Livewire\LaundryServices\Table::class)->name('index');
    Route::get('create', App\Livewire\LaundryServices\CreatePage::class)->name('create');
    Route::get('{service}/edit', App\Livewire\LaundryServices\UpdatePage::class)->name('edit');
});

Route::prefix('service-price-lists')->middleware('auth')->name('service-price-lists.')->group(function () {
    Route::get('/', App\Livewire\ServicePriceLists\Table::class)->name('index');
    Route::get('/create', App\Livewire\ServicePriceLists\CreatePage::class)->name('create');
    Route::get('/{servicePriceList}/edit', App\Livewire\ServicePriceLists\UpdatePage::class)->name('edit');
});

Route::prefix('qr-codes')->middleware('auth')->name('qr-codes.')->group(function () {
    Route::get('', App\Livewire\Qr\QrCodeGenerator::class)->name('index');
});

Route::prefix('processing-costs')->middleware('auth')->name('processing-costs.')->group(function () {
    Route::get('', App\Livewire\ProcessingCosts\Table::class)->name('index');
    Route::get('create', App\Livewire\ProcessingCosts\CreatePage::class)->name('create');
    Route::get('{processingCost}/edit', App\Livewire\ProcessingCosts\UpdatePage::class)->name('edit');
});

Route::middleware('auth')->get('/download-pdf/{filename}', function ($filename) {
    if (!str_ends_with($filename, '.pdf') || str_contains($filename, '..')) {
        abort(404);
    }

    $path = storage_path('app/public/' . $filename);

    if (!file_exists($path)) {
        abort(404, 'File not found');
    }

    return response()->download($path, $filename, [
        'Content-Type' => 'application/pdf',
    ])->deleteFileAfterSend(false);
})->name('download.pdf');
