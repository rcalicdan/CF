<?php

use App\Models\Order;
use App\Models\Client;
use App\Models\Driver;
use App\Models\User;
use App\Models\OrderHistory;
use App\Enums\OrderStatus;
use App\Jobs\SendSmsJob;
use App\Observers\OrderObserver;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;

beforeEach(function () {
    $this->user = User::factory()->create([
        'first_name' => 'Test',
        'last_name' => 'User'
    ]);
    
    $this->client = Client::factory()->create([
        'first_name' => 'Jan',
        'last_name' => 'Kowalski',
        'phone_number' => '+48123456789'
    ]);
    
    Auth::login($this->user);
    Config::set('app.url', 'http://127.0.0.1:8000');
});

it('creates order history when order is created', function () {
    $order = Order::factory()->create([
        'client_id' => $this->client->id,
        'status' => OrderStatus::PENDING->value
    ]);

    expect($order->orderHistories)
        ->toHaveCount(1)
        ->and($order->orderHistories->first())
        ->user_id->toBe($this->user->id)
        ->old_status->toBeNull()
        ->new_status->toBe(OrderStatus::PENDING->value)
        ->action_type->toBe('created')
        ->notes->toBe('Zamówienie zostało utworzone');
});

it('logs status change and sends sms when order is delivered', function () {
    Queue::fake();
    Log::spy();

    $order = Order::factory()->create([
        'client_id' => $this->client->id,
        'status' => OrderStatus::PROCESSING->value
    ]);
    
    $order->update(['status' => OrderStatus::DELIVERED->value]);

    // Assert order history was created
    $deliveryHistory = $order->orderHistories()
        ->where('action_type', 'delivered')
        ->first();

    expect($deliveryHistory)
        ->not->toBeNull()
        ->old_status->toBe(OrderStatus::PROCESSING->value)
        ->new_status->toBe(OrderStatus::DELIVERED->value)
        ->notes->toBe('Zamówienie zostało dostarczone');

    Queue::assertPushed(SendSmsJob::class, function ($job) use ($order) {
        return $job->phone_number === $this->client->phone_number &&
               str_contains($job->message, "Dziękujemy {$this->client->first_name}!") &&
               str_contains($job->message, "Zamówienie #{$order->id}") &&
               str_contains($job->message, 'http://127.0.0.1:8000/reviews');
    });

    Log::shouldHaveReceived('info')
        ->once()
        ->with('Delivery thank you SMS queued', \Mockery::on(function ($data) use ($order) {
            return $data['order_id'] === $order->id &&
                   $data['client_id'] === $order->client_id &&
                   $data['phone_number'] === $this->client->phone_number &&
                   str_contains($data['message'], "Dziękujemy {$this->client->first_name}!");
        }));
});

it('does not send sms when client has no phone number', function () {
    Queue::fake();
    Log::spy();

    $clientWithoutPhone = Client::factory()->create([
        'phone_number' => null
    ]);

    $order = Order::factory()->create([
        'client_id' => $clientWithoutPhone->id,
        'status' => OrderStatus::PROCESSING->value
    ]);

    $order->update(['status' => OrderStatus::DELIVERED->value]);

    Queue::assertNotPushed(SendSmsJob::class);

    Log::shouldHaveReceived('warning')
        ->once()
        ->with('Cannot send delivery SMS: Client has no phone number', [
            'order_id' => $order->id,
            'client_id' => $clientWithoutPhone->id
        ]);
});

it('does not send sms for non-delivered status changes', function () {
    Queue::fake();

    $order = Order::factory()->create([
        'client_id' => $this->client->id,
        'status' => OrderStatus::PENDING->value
    ]);

    $order->update(['status' => OrderStatus::ACCEPTED->value]);
    $order->update(['status' => OrderStatus::PROCESSING->value]);
    $order->update(['status' => OrderStatus::COMPLETED->value]);
    $order->update(['status' => OrderStatus::CANCELED->value]);

    Queue::assertNotPushed(SendSmsJob::class);
});

it('logs driver assignment correctly', function () {
    $driver = Driver::factory()->create();
    $driverUser = User::factory()->create([
        'first_name' => 'Driver',
        'last_name' => 'Test'
    ]);
    $driver->user()->associate($driverUser);
    $driver->save();

    $order = Order::factory()->create([
        'client_id' => $this->client->id,
        'assigned_driver_id' => null
    ]);

    $order->update(['assigned_driver_id' => $driver->id]);

    $driverHistory = $order->orderHistories()
        ->where('action_type', 'assigned')
        ->first();

    expect($driverHistory)
        ->not->toBeNull()
        ->action_type->toBe('assigned')
        ->notes->toContain("Kierowca 'Driver Test' został przypisany do zamówienia");

    expect($driverHistory->changes)
        ->toBeArray()
        ->toHaveKey('assigned_driver_id')
        ->and($driverHistory->changes['assigned_driver_id']['old'])->toBeNull()
        ->and($driverHistory->changes['assigned_driver_id']['new'])->toBe($driver->id);
});

it('logs schedule changes correctly', function () {
    $oldDate = now()->subDay();
    $newDate = now()->addDay();

    $order = Order::factory()->create([
        'client_id' => $this->client->id,
        'schedule_date' => $oldDate
    ]);

    $order->update(['schedule_date' => $newDate]);

    $scheduleHistory = $order->orderHistories()
        ->where('action_type', 'schedule_updated')
        ->first();

    expect($scheduleHistory)
        ->not->toBeNull()
        ->action_type->toBe('schedule_updated')
        ->notes->toContain('Data realizacji zmieniona z');

    expect($scheduleHistory->changes)
        ->toBeArray()
        ->toHaveKey('schedule_date');
});

it('logs amount changes correctly', function () {
    $order = Order::factory()->create([
        'client_id' => $this->client->id,
        'total_amount' => 100.00
    ]);

    $order->update(['total_amount' => 150.50]);

    $amountHistory = $order->orderHistories()
        ->where('action_type', 'amount_updated')
        ->first();

    expect($amountHistory)
        ->not->toBeNull()
        ->action_type->toBe('amount_updated')
        ->notes->toContain('Kwota całkowita zmieniona z 100,00 PLN na 150,50 PLN');

    expect($amountHistory->changes)
        ->toBeArray()
        ->toHaveKey('total_amount')
        ->and($amountHistory->changes['total_amount']['old'])->toBe(100.00)
        ->and($amountHistory->changes['total_amount']['new'])->toBe(150.50);
});

it('logs client changes correctly', function () {
    $newClient = Client::factory()->create([
        'first_name' => 'Maria',
        'last_name' => 'Nowak'
    ]);

    $order = Order::factory()->create([
        'client_id' => $this->client->id
    ]);

    $order->update(['client_id' => $newClient->id]);

    $clientHistory = $order->orderHistories()
        ->where('action_type', 'client_updated')
        ->first();

    expect($clientHistory)
        ->not->toBeNull()
        ->action_type->toBe('client_updated')
        ->notes->toContain("Klient zmieniony z 'Jan Kowalski' na 'Maria Nowak'");

    expect($clientHistory->changes)
        ->toBeArray()
        ->toHaveKey('client_id')
        ->and($clientHistory->changes['client_id']['old'])->toBe($this->client->id)
        ->and($clientHistory->changes['client_id']['new'])->toBe($newClient->id);
});

it('logs complaint status changes correctly', function () {
    $order = Order::factory()->create([
        'client_id' => $this->client->id,
        'is_complaint' => false
    ]);

    // Mark as complaint
    $order->update(['is_complaint' => true]);

    $complaintHistory = $order->orderHistories()
        ->where('action_type', 'complaint_updated')
        ->first();

    expect($complaintHistory)
        ->not->toBeNull()
        ->action_type->toBe('complaint_updated')
        ->notes->toBe('Zamówienie zostało oznaczone jako reklamacja');

    expect($complaintHistory->changes)
        ->toBeArray()
        ->toHaveKey('is_complaint')
        ->and($complaintHistory->changes['is_complaint']['old'])->toBeFalse()
        ->and($complaintHistory->changes['is_complaint']['new'])->toBeTrue();
});

it('generates correct sms message content', function () {
    Queue::fake();

    $order = Order::factory()->create([
        'client_id' => $this->client->id,
        'status' => OrderStatus::PROCESSING->value
    ]);

    $order->update(['status' => OrderStatus::DELIVERED->value]);

    Queue::assertPushed(SendSmsJob::class, function ($job) use ($order) {
        $expectedMessage = sprintf(
            "Dziękujemy %s! Zamówienie #%d zostało pomyślnie dostarczone. Będziemy wdzięczni za Twoją opinię: %s",
            $this->client->first_name,
            $order->id,
            'http://127.0.0.1:8000/reviews'
        );
        
        return $job->phone_number === $this->client->phone_number &&
               $job->message === $expectedMessage;
    });
});

it('handles client without first name in sms message', function () {
    Queue::fake();

    $clientWithoutFirstName = Client::factory()->create([
        'first_name' => null,
        'last_name' => 'Kowalski',
        'phone_number' => '+48123456789'
    ]);

    $order = Order::factory()->create([
        'client_id' => $clientWithoutFirstName->id,
        'status' => OrderStatus::PROCESSING->value
    ]);

    $order->update(['status' => OrderStatus::DELIVERED->value]);

    Queue::assertPushed(SendSmsJob::class, function ($job) use ($order) {
        return str_contains($job->message, 'Dziękujemy szanowny kliencie!');
    });
});

it('uses correct app url from config for review link', function () {
    Queue::fake();
    Config::set('app.url', 'https://example.com');

    $order = Order::factory()->create([
        'client_id' => $this->client->id,
        'status' => OrderStatus::PROCESSING->value
    ]);

    $order->update(['status' => OrderStatus::DELIVERED->value]);

    Queue::assertPushed(SendSmsJob::class, function ($job) {
        return str_contains($job->message, 'https://example.com/reviews');
    });
});

it('can log static carpet and service events', function () {
    $order = Order::factory()->create([
        'client_id' => $this->client->id
    ]);

    OrderObserver::logCarpetAdded($order, 123);
    OrderObserver::logCarpetRemoved($order, 456);
    OrderObserver::logServiceAdded($order, 789, 'Pranie dywanów');

    $histories = $order->orderHistories;

    expect($histories)
        ->toHaveCount(4) 
        ->and($histories->where('action_type', 'carpet_added')->first())
        ->notes->toBe('Nowy dywan został dodany do zamówienia (ID: 123)')
        ->and($histories->where('action_type', 'carpet_removed')->first())
        ->notes->toBe('Dywan został usunięty z zamówienia (ID: 456)')
        ->and($histories->where('action_type', 'service_added')->first())
        ->notes->toBe("Usługa 'Pranie dywanów' została dodana do zamówienia");
});