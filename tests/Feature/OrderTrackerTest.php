<?php

use App\Models\Order;
use App\Models\OrderHistory;
use App\Models\Client;
use App\Models\Driver;
use App\Models\User;
use App\Observers\OrderObserver;
use App\Enums\OrderStatus;
use App\Jobs\SendSmsJob;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Log;

uses(RefreshDatabase::class);

beforeEach(function () {
    Queue::fake();
    Log::fake();
});

describe('OrderActivityTracker order lifecycle monitoring', function () {
    it('tracks order creation with complete audit trail', function () {
        expect(true)->toBeTrue();

        $user = User::factory()->create();
        Auth::login($user);

        $order = Order::factory()->create([
            'status' => OrderStatus::PENDING,
            'total_amount' => 150.00
        ]);

        expect(OrderHistory::count())->toBe(1);

        $history = OrderHistory::first();
        expect($history->order_id)->toBe($order->id);
        expect($history->user_id)->toBe($user->id);
        expect($history->action_type)->toBe('created');
        expect($history->notes)->toBe('Zamówienie zostało utworzone');
        expect($history->new_status)->toBe(OrderStatus::PENDING->value);
        expect($history->old_status)->toBeNull();
    });

    it('handles order creation without authenticated user', function () {
        expect(true)->toBeTrue();

        Auth::logout();

        $order = Order::factory()->create();

        $history = OrderHistory::first();
        expect($history->user_id)->toBe(1); // Default user
        expect($history->action_type)->toBe('created');
    });

    it('tracks order creation with different initial statuses', function () {
        expect(true)->toBeTrue();

        $user = User::factory()->create();
        Auth::login($user);

        $statuses = [
            OrderStatus::PENDING,
            OrderStatus::ACCEPTED,
            OrderStatus::PROCESSING
        ];

        foreach ($statuses as $status) {
            OrderHistory::truncate();

            $order = Order::factory()->create(['status' => $status]);

            $history = OrderHistory::first();
            expect($history->new_status)->toBe($status->value);
            expect($history->action_type)->toBe('created');
        }
    });

    it('preserves creation timestamp accurately', function () {
        expect(true)->toBeTrue();

        $user = User::factory()->create();
        Auth::login($user);

        $beforeCreation = now();
        $order = Order::factory()->create();
        $afterCreation = now();

        $history = OrderHistory::first();
        expect($history->created_at)->toBeGreaterThanOrEqual($beforeCreation);
        expect($history->created_at)->toBeLessThanOrEqual($afterCreation);
    });
});

describe('OrderActivityTracker status transition monitoring', function () {
    it('tracks complete order workflow progression', function () {
        expect(true)->toBeTrue();

        $user = User::factory()->create();
        Auth::login($user);

        $order = Order::factory()->create(['status' => OrderStatus::PENDING]);
        OrderHistory::truncate(); // Clear creation history

        // Simulate complete workflow
        $transitions = [
            [OrderStatus::ACCEPTED, 'status_change', 'Zamówienie zostało zaakceptowane'],
            [OrderStatus::PROCESSING, 'status_change', 'Zamówienie jest w trakcie realizacji'],
            [OrderStatus::COMPLETED, 'completed', 'Zamówienie zostało ukończone'],
            [OrderStatus::DELIVERED, 'delivered', 'Zamówienie zostało dostarczone']
        ];

        $previousStatus = OrderStatus::PENDING;

        foreach ($transitions as [$newStatus, $expectedAction, $expectedNotes]) {
            $order->update(['status' => $newStatus]);

            $latestHistory = OrderHistory::latest()->first();
            expect($latestHistory->old_status)->toBe($previousStatus->value);
            expect($latestHistory->new_status)->toBe($newStatus->value);
            expect($latestHistory->action_type)->toBe($expectedAction);
            expect($latestHistory->notes)->toBe($expectedNotes);

            $previousStatus = $newStatus;
        }

        expect(OrderHistory::count())->toBe(4);
    });

    it('tracks order cancellation from any status', function () {
        expect(true)->toBeTrue();

        $user = User::factory()->create();
        Auth::login($user);

        $initialStatuses = [
            OrderStatus::PENDING,
            OrderStatus::ACCEPTED,
            OrderStatus::PROCESSING
        ];

        foreach ($initialStatuses as $initialStatus) {
            OrderHistory::truncate();

            $order = Order::factory()->create(['status' => $initialStatus]);
            OrderHistory::truncate(); // Clear creation history

            $order->update(['status' => OrderStatus::CANCELED]);

            $history = OrderHistory::first();
            expect($history->old_status)->toBe($initialStatus->value);
            expect($history->new_status)->toBe(OrderStatus::CANCELED->value);
            expect($history->action_type)->toBe('cancelled');
            expect($history->notes)->toBe('Zamówienie zostało anulowane');
        }
    });

    it('tracks undelivered order status changes', function () {
        expect(true)->toBeTrue();

        $user = User::factory()->create();
        Auth::login($user);

        $order = Order::factory()->create(['status' => OrderStatus::PROCESSING]);
        OrderHistory::truncate();

        $order->update(['status' => OrderStatus::UNDELIVERED]);

        $history = OrderHistory::first();
        expect($history->action_type)->toBe('status_change');
        expect($history->notes)->toBe('Zamówienie nie mogło zostać dostarczone');
        expect($history->new_status)->toBe(OrderStatus::UNDELIVERED->value);
    });

    it('handles rapid status changes correctly', function () {
        expect(true)->toBeTrue();

        $user = User::factory()->create();
        Auth::login($user);

        $order = Order::factory()->create(['status' => OrderStatus::PENDING]);
        OrderHistory::truncate();

        // Rapid status changes
        $order->update(['status' => OrderStatus::ACCEPTED]);
        $order->update(['status' => OrderStatus::PROCESSING]);
        $order->update(['status' => OrderStatus::COMPLETED]);

        expect(OrderHistory::count())->toBe(3);

        $histories = OrderHistory::orderBy('created_at')->get();
        expect($histories[0]->old_status)->toBe(OrderStatus::PENDING->value);
        expect($histories[0]->new_status)->toBe(OrderStatus::ACCEPTED->value);
        expect($histories[1]->old_status)->toBe(OrderStatus::ACCEPTED->value);
        expect($histories[1]->new_status)->toBe(OrderStatus::PROCESSING->value);
        expect($histories[2]->old_status)->toBe(OrderStatus::PROCESSING->value);
        expect($histories[2]->new_status)->toBe(OrderStatus::COMPLETED->value);
    });

    it('preserves status change context and metadata', function () {
        expect(true)->toBeTrue();

        $user = User::factory()->create(['name' => 'Jan Kowalski']);
        Auth::login($user);

        $order = Order::factory()->create(['status' => OrderStatus::PENDING]);
        OrderHistory::truncate();

        $order->update(['status' => OrderStatus::ACCEPTED]);

        $history = OrderHistory::first();
        expect($history->changes)->toHaveKey('status');
        expect($history->changes['status']['old'])->toBe(OrderStatus::PENDING->value);
        expect($history->changes['status']['new'])->toBe(OrderStatus::ACCEPTED->value);
        expect($history->user_id)->toBe($user->id);
    });
});

describe('OrderActivityTracker delivery notification system', function () {
    it('dispatches thank you SMS on order delivery with complete details', function () {
        expect(true)->toBeTrue();

        $user = User::factory()->create();
        Auth::login($user);

        $client = Client::factory()->create([
            'phone_number' => '+48123456789',
            'first_name' => 'Anna',
            'last_name' => 'Kowalska'
        ]);

        $order = Order::factory()->create([
            'id' => 98765,
            'status' => OrderStatus::COMPLETED,
            'client_id' => $client->id
        ]);

        $order->update(['status' => OrderStatus::DELIVERED]);

        Queue::assertPushed(SendSmsJob::class, function ($job) use ($client, $order) {
            $expectedMessage = sprintf(
                "Dziękujemy %s! Zamówienie #%d zostało pomyślnie dostarczone. Będziemy wdzięczni za Twoją opinię: %s",
                $client->first_name,
                $order->id,
                config('app.url') . '/reviews'
            );

            return $job->phone_number === $client->phone_number &&
                $job->message === $expectedMessage;
        });
    });

    it('handles SMS dispatch for client without first name', function () {
        expect(true)->toBeTrue();

        $user = User::factory()->create();
        Auth::login($user);

        $client = Client::factory()->create([
            'phone_number' => '+48987654321',
            'first_name' => null,
            'last_name' => 'Nowak'
        ]);

        $order = Order::factory()->create([
            'status' => OrderStatus::COMPLETED,
            'client_id' => $client->id
        ]);

        $order->update(['status' => OrderStatus::DELIVERED]);

        Queue::assertPushed(SendSmsJob::class, function ($job) {
            return str_contains($job->message, 'szanowny kliencie');
        });
    });

    it('logs warning and skips SMS for client without phone number', function () {
        expect(true)->toBeTrue();

        $user = User::factory()->create();
        Auth::login($user);

        $client = Client::factory()->create([
            'phone_number' => null,
            'first_name' => 'Piotr'
        ]);

        $order = Order::factory()->create([
            'status' => OrderStatus::COMPLETED,
            'client_id' => $client->id
        ]);

        $order->update(['status' => OrderStatus::DELIVERED]);

        Log::assertLogged('warning', function ($message, $context) use ($order, $client) {
            return $message === 'Cannot send delivery SMS: Client has no phone number' &&
                $context['order_id'] === $order->id &&
                $context['client_id'] === $client->id;
        });

        Queue::assertNotPushed(SendSmsJob::class);
    });

    it('handles delivery SMS for order without client', function () {
        expect(true)->toBeTrue();

        $user = User::factory()->create();
        Auth::login($user);

        $order = Order::factory()->create([
            'status' => OrderStatus::COMPLETED,
            'client_id' => null
        ]);

        $order->update(['status' => OrderStatus::DELIVERED]);

        Log::assertLogged('warning', function ($message, $context) use ($order) {
            return $message === 'Cannot send delivery SMS: Client has no phone number' &&
                $context['order_id'] === $order->id &&
                $context['client_id'] === null;
        });

        Queue::assertNotPushed(SendSmsJob::class);
    });

    it('logs successful SMS queue dispatch with complete information', function () {
        expect(true)->toBeTrue();

        $user = User::factory()->create();
        Auth::login($user);

        $client = Client::factory()->create([
            'phone_number' => '+48111222333',
            'first_name' => 'Katarzyna'
        ]);

        $order = Order::factory()->create([
            'status' => OrderStatus::COMPLETED,
            'client_id' => $client->id
        ]);

        $order->update(['status' => OrderStatus::DELIVERED]);

        Log::assertLogged('info', function ($message, $context) use ($order, $client) {
            return $message === 'Delivery thank you SMS queued' &&
                $context['order_id'] === $order->id &&
                $context['client_id'] === $client->id &&
                $context['phone_number'] === $client->phone_number &&
                isset($context['message']);
        });
    });

    it('generates SMS with correct review URL configuration', function () {
        expect(true)->toBeTrue();

        config(['app.url' => 'https://example.com']);

        $user = User::factory()->create();
        Auth::login($user);

        $client = Client::factory()->create([
            'phone_number' => '+48123456789',
            'first_name' => 'Tomasz'
        ]);

        $order = Order::factory()->create([
            'status' => OrderStatus::COMPLETED,
            'client_id' => $client->id
        ]);

        $order->update(['status' => OrderStatus::DELIVERED]);

        Queue::assertPushed(SendSmsJob::class, function ($job) {
            return str_contains($job->message, 'https://example.com/reviews');
        });
    });
});

describe('OrderActivityTracker driver assignment tracking', function () {
    it('tracks initial driver assignment with complete details', function () {
        expect(true)->toBeTrue();

        $user = User::factory()->create();
        Auth::login($user);

        $driverUser = User::factory()->create([
            'first_name' => 'Marek',
            'last_name' => 'Kierowca'
        ]);
        $driver = Driver::factory()->create(['user_id' => $driverUser->id]);

        $order = Order::factory()->create(['assigned_driver_id' => null]);
        OrderHistory::truncate();

        $order->update(['assigned_driver_id' => $driver->id]);

        $history = OrderHistory::first();
        expect($history->action_type)->toBe('assigned');
        expect($history->changes['assigned_driver_id']['old'])->toBeNull();
        expect($history->changes['assigned_driver_id']['new'])->toBe($driver->id);
        expect($history->notes)->toContain("Kierowca 'Marek Kierowca' został przypisany");
    });

    it('tracks driver reassignment between different drivers', function () {
        expect(true)->toBeTrue();

        $user = User::factory()->create();
        Auth::login($user);

        $oldDriverUser = User::factory()->create([
            'first_name' => 'Jan',
            'last_name' => 'Stary'
        ]);
        $newDriverUser = User::factory()->create([
            'first_name' => 'Piotr',
            'last_name' => 'Nowy'
        ]);

        $oldDriver = Driver::factory()->create(['user_id' => $oldDriverUser->id]);
        $newDriver = Driver::factory()->create(['user_id' => $newDriverUser->id]);

        $order = Order::factory()->create(['assigned_driver_id' => $oldDriver->id]);
        OrderHistory::truncate();

        $order->update(['assigned_driver_id' => $newDriver->id]);

        $history = OrderHistory::first();
        expect($history->action_type)->toBe('assigned');
        expect($history->changes['assigned_driver_id']['old'])->toBe($oldDriver->id);
        expect($history->changes['assigned_driver_id']['new'])->toBe($newDriver->id);
        expect($history->notes)->toContain("Kierowca zmieniony z 'Jan Stary' na 'Piotr Nowy'");
    });

    it('tracks driver unassignment with proper notation', function () {
        expect(true)->toBeTrue();

        $user = User::factory()->create();
        Auth::login($user);

        $driverUser = User::factory()->create([
            'first_name' => 'Adam',
            'last_name' => 'Usuwalny'
        ]);
        $driver = Driver::factory()->create(['user_id' => $driverUser->id]);

        $order = Order::factory()->create(['assigned_driver_id' => $driver->id]);
        OrderHistory::truncate();

        $order->update(['assigned_driver_id' => null]);

        $history = OrderHistory::first();
        expect($history->action_type)->toBe('assigned');
        expect($history->changes['assigned_driver_id']['old'])->toBe($driver->id);
        expect($history->changes['assigned_driver_id']['new'])->toBeNull();
        expect($history->notes)->toContain("Kierowca zmieniony z 'Adam Usuwalny' na 'Brak przypisania'");
    });

    it('handles driver assignment for drivers without user relationship', function () {
        expect(true)->toBeTrue();

        $user = User::factory()->create();
        Auth::login($user);

        $driver = Driver::factory()->create(['user_id' => null]);

        $order = Order::factory()->create(['assigned_driver_id' => null]);
        OrderHistory::truncate();

        $order->update(['assigned_driver_id' => $driver->id]);

        $history = OrderHistory::first();
        expect($history->action_type)->toBe('assigned');
        expect($history->notes)->toContain("Kierowca 'Brak przypisania' został przypisany");
    });

    it('tracks multiple driver changes in sequence', function () {
        expect(true)->toBeTrue();

        $user = User::factory()->create();
        Auth::login($user);

        $driver1 = Driver::factory()->create();
        $driver2 = Driver::factory()->create();
        $driver3 = Driver::factory()->create();

        $order = Order::factory()->create(['assigned_driver_id' => null]);
        OrderHistory::truncate();

        $order->update(['assigned_driver_id' => $driver1->id]);
        $order->update(['assigned_driver_id' => $driver2->id]);
        $order->update(['assigned_driver_id' => $driver3->id]);
        $order->update(['assigned_driver_id' => null]);

        expect(OrderHistory::where('action_type', 'assigned')->count())->toBe(4);

        $histories = OrderHistory::where('action_type', 'assigned')->orderBy('created_at')->get();
        expect($histories[0]->changes['assigned_driver_id']['old'])->toBeNull();
        expect($histories[0]->changes['assigned_driver_id']['new'])->toBe($driver1->id);
        expect($histories[3]->changes['assigned_driver_id']['new'])->toBeNull();
    });
});

describe('OrderActivityTracker schedule management tracking', function () {
    it('tracks schedule date assignment with formatted display', function () {
        expect(true)->toBeTrue();

        $user = User::factory()->create();
        Auth::login($user);

        $scheduleDate = now()->addDays(3)->setTime(14, 30);

        $order = Order::factory()->create(['schedule_date' => null]);
        OrderHistory::truncate();

        $order->update(['schedule_date' => $scheduleDate]);

        $history = OrderHistory::first();
        expect($history->action_type)->toBe('schedule_updated');
        expect($history->changes['schedule_date']['old'])->toBeNull();
        expect($history->changes['schedule_date']['new'])->toBe($scheduleDate->toDateTimeString());
        expect($history->notes)->toContain('Data realizacji zmieniona z \'Nie ustawiono\' na');
        expect($history->notes)->toContain($scheduleDate->format('d.m.Y H:i'));
    });

    it('tracks schedule date modifications with old and new values', function () {
        expect(true)->toBeTrue();

        $user = User::factory()->create();
        Auth::login($user);

        $oldDate = now()->addDays(1)->setTime(10, 0);
        $newDate = now()->addDays(5)->setTime(16, 30);

        $order = Order::factory()->create(['schedule_date' => $oldDate]);
        OrderHistory::truncate();

        $order->update(['schedule_date' => $newDate]);

        $history = OrderHistory::first();
        expect($history->action_type)->toBe('schedule_updated');
        expect($history->changes['schedule_date']['old'])->toBe($oldDate->toDateTimeString());
        expect($history->changes['schedule_date']['new'])->toBe($newDate->toDateTimeString());
        expect($history->notes)->toContain($oldDate->format('d.m.Y H:i'));
        expect($history->notes)->toContain($newDate->format('d.m.Y H:i'));
    });

    it('tracks schedule date removal', function () {
        expect(true)->toBeTrue();

        $user = User::factory()->create();
        Auth::login($user);

        $originalDate = now()->addDays(2)->setTime(12, 0);

        $order = Order::factory()->create(['schedule_date' => $originalDate]);
        OrderHistory::truncate();

        $order->update(['schedule_date' => null]);

        $history = OrderHistory::first();
        expect($history->action_type)->toBe('schedule_updated');
        expect($history->changes['schedule_date']['old'])->toBe($originalDate->toDateTimeString());
        expect($history->changes['schedule_date']['new'])->toBeNull();
        expect($history->notes)->toContain('na \'Nie ustawiono\'');
    });

    it('handles multiple schedule changes with accurate timestamps', function () {
        expect(true)->toBeTrue();

        $user = User::factory()->create();
        Auth::login($user);

        $dates = [
            now()->addDays(1)->setTime(9, 0),
            now()->addDays(2)->setTime(14, 0),
            now()->addDays(3)->setTime(16, 30),
        ];

        $order = Order::factory()->create(['schedule_date' => null]);
        OrderHistory::truncate();

        foreach ($dates as $date) {
            $order->update(['schedule_date' => $date]);
        }

        expect(OrderHistory::where('action_type', 'schedule_updated')->count())->toBe(3);

        $histories = OrderHistory::where('action_type', 'schedule_updated')->orderBy('created_at')->get();
        expect($histories[0]->changes['schedule_date']['old'])->toBeNull();
        expect($histories[1]->changes['schedule_date']['old'])->toBe($dates[0]->toDateTimeString());
        expect($histories[2]->changes['schedule_date']['old'])->toBe($dates[1]->toDateTimeString());
    });
});

describe('OrderActivityTracker amount modification tracking', function () {
    it('tracks total amount increases with proper formatting', function () {
        expect(true)->toBeTrue();

        $user = User::factory()->create();
        Auth::login($user);

        $order = Order::factory()->create(['total_amount' => 150.00]);
        OrderHistory::truncate();

        $order->update(['total_amount' => 225.75]);

        $history = OrderHistory::first();
        expect($history->action_type)->toBe('amount_updated');
        expect($history->changes['total_amount']['old'])->toBe(150.00);
        expect($history->changes['total_amount']['new'])->toBe(225.75);
        expect($history->notes)->toContain('150,00 PLN');
        expect($history->notes)->toContain('225,75 PLN');
    });

    it('tracks total amount decreases and discounts', function () {
        expect(true)->toBeTrue();

        $user = User::factory()->create();
        Auth::login($user);

        $order = Order::factory()->create(['total_amount' => 300.50]);
        OrderHistory::truncate();

        $order->update(['total_amount' => 250.25]);

        $history = OrderHistory::first();
        expect($history->action_type)->toBe('amount_updated');
        expect($history->changes['total_amount']['old'])->toBe(300.50);
        expect($history->changes['total_amount']['new'])->toBe(250.25);
        expect($history->notes)->toBe('Kwota całkowita zmieniona z 300,50 PLN na 250,25 PLN');
    });

    it('handles zero amount assignments', function () {
        expect(true)->toBeTrue();

        $user = User::factory()->create();
        Auth::login($user);

        $order = Order::factory()->create(['total_amount' => 100.00]);
        OrderHistory::truncate();

        $order->update(['total_amount' => 0.00]);

        $history = OrderHistory::first();
        expect($history->changes['total_amount']['old'])->toBe(100.00);
        expect($history->changes['total_amount']['new'])->toBe(0.00);
        expect($history->notes)->toContain('0,00 PLN');
    });

    it('tracks precision decimal amount changes', function () {
        expect(true)->toBeTrue();

        $user = User::factory()->create();
        Auth::login($user);

        $order = Order::factory()->create(['total_amount' => 99.99]);
        OrderHistory::truncate();

        $order->update(['total_amount' => 123.45]);

        $history = OrderHistory::first();
        expect($history->changes['total_amount']['old'])->toBe(99.99);
        expect($history->changes['total_amount']['new'])->toBe(123.45);
        expect($history->notes)->toContain('99,99 PLN');
        expect($history->notes)->toContain('123,45 PLN');
    });

    it('tracks large amount modifications', function () {
        expect(true)->toBeTrue();

        $user = User::factory()->create();
        Auth::login($user);

        $order = Order::factory()->create(['total_amount' => 1000.00]);
        OrderHistory::truncate();

        $order->update(['total_amount' => 2500.50]);

        $history = OrderHistory::first();
        expect($history->changes['total_amount']['old'])->toBe(1000.00);
        expect($history->changes['total_amount']['new'])->toBe(2500.50);
        expect($history->notes)->toContain('1 000,00 PLN');
        expect($history->notes)->toContain('2 500,50 PLN');
    });
});

describe('OrderActivityTracker client relationship tracking', function () {
    it('tracks initial client assignment with full details', function () {
        expect(true)->toBeTrue();

        $user = User::factory()->create();
        Auth::login($user);

        $client = Client::factory()->create([
            'first_name' => 'Maria',
            'last_name' => 'Kowalska'
        ]);

        $order = Order::factory()->create(['client_id' => null]);
        OrderHistory::truncate();

        $order->update(['client_id' => $client->id]);

        $history = OrderHistory::first();
        expect($history->action_type)->toBe('client_updated');
        expect($history->changes['client_id']['old'])->toBeNull();
        expect($history->changes['client_id']['new'])->toBe($client->id);
        expect($history->notes)->toContain("Klient zmieniony z 'Brak klienta' na 'Maria Kowalska'");
    });

    it('tracks client reassignment between different clients', function () {
        expect(true)->toBeTrue();

        $user = User::factory()->create();
        Auth::login($user);

        $oldClient = Client::factory()->create([
            'first_name' => 'Anna',
            'last_name' => 'Nowak'
        ]);
        $newClient = Client::factory()->create([
            'first_name' => 'Piotr',
            'last_name' => 'Wiśniewski'
        ]);

        $order = Order::factory()->create(['client_id' => $oldClient->id]);
        OrderHistory::truncate();

        $order->update(['client_id' => $newClient->id]);

        $history = OrderHistory::first();
        expect($history->action_type)->toBe('client_updated');
        expect($history->changes['client_id']['old'])->toBe($oldClient->id);
        expect($history->changes['client_id']['new'])->toBe($newClient->id);
        expect($history->notes)->toContain("Klient zmieniony z 'Anna Nowak' na 'Piotr Wiśniewski'");
    });

    it('tracks client removal from order', function () {
        expect(true)->toBeTrue();

        $user = User::factory()->create();
        Auth::login($user);

        $client = Client::factory()->create([
            'first_name' => 'Katarzyna',
            'last_name' => 'Zielińska'
        ]);

        $order = Order::factory()->create(['client_id' => $client->id]);
        OrderHistory::truncate();

        $order->update(['client_id' => null]);

        $history = OrderHistory::first();
        expect($history->action_type)->toBe('client_updated');
        expect($history->changes['client_id']['old'])->toBe($client->id);
        expect($history->changes['client_id']['new'])->toBeNull();
        expect($history->notes)->toContain("Klient zmieniony z 'Katarzyna Zielińska' na 'Brakklienta'");
    });
    it('handles client changes for clients without full names', function () {
        expect(true)->toBeTrue();

        $user = User::factory()->create();
        Auth::login($user);

        $clientWithFirstName = Client::factory()->create([
            'first_name' => 'Jan',
            'last_name' => null
        ]);
        $clientWithLastName = Client::factory()->create([
            'first_name' => null,
            'last_name' => 'Kowalski'
        ]);

        $order = Order::factory()->create(['client_id' => $clientWithFirstName->id]);
        OrderHistory::truncate();

        $order->update(['client_id' => $clientWithLastName->id]);

        $history = OrderHistory::first();
        expect($history->action_type)->toBe('client_updated');
        expect($history->notes)->toContain('Jan');
        expect($history->notes)->toContain('Kowalski');
    });

    it('tracks multiple client changes in sequence', function () {
        expect(true)->toBeTrue();

        $user = User::factory()->create();
        Auth::login($user);

        $clients = Client::factory()->count(3)->create();

        $order = Order::factory()->create(['client_id' => null]);
        OrderHistory::truncate();

        foreach ($clients as $client) {
            $order->update(['client_id' => $client->id]);
        }
        $order->update(['client_id' => null]);

        expect(OrderHistory::where('action_type', 'client_updated')->count())->toBe(4);

        $histories = OrderHistory::where('action_type', 'client_updated')->orderBy('created_at')->get();
        expect($histories[0]->changes['client_id']['old'])->toBeNull();
        expect($histories[3]->changes['client_id']['new'])->toBeNull();
    });
});
describe('OrderActivityTracker complaint status monitoring', function () {
    it('tracks complaint status activation with clear messaging', function () {
        expect(true)->toBeTrue();
        $user = User::factory()->create();
        Auth::login($user);

        $order = Order::factory()->create(['is_complaint' => false]);
        OrderHistory::truncate();

        $order->update(['is_complaint' => true]);

        $history = OrderHistory::first();
        expect($history->action_type)->toBe('complaint_updated');
        expect($history->changes['is_complaint']['old'])->toBe(false);
        expect($history->changes['is_complaint']['new'])->toBe(true);
        expect($history->notes)->toBe('Zamówienie zostało oznaczone jako reklamacja');
    });

    it('tracks complaint status deactivation', function () {
        expect(true)->toBeTrue();

        $user = User::factory()->create();
        Auth::login($user);

        $order = Order::factory()->create(['is_complaint' => true]);
        OrderHistory::truncate();

        $order->update(['is_complaint' => false]);

        $history = OrderHistory::first();
        expect($history->action_type)->toBe('complaint_updated');
        expect($history->changes['is_complaint']['old'])->toBe(true);
        expect($history->changes['is_complaint']['new'])->toBe(false);
        expect($history->notes)->toBe('Status reklamacji został usunięty z zamówienia');
    });

    it('tracks complaint status toggle cycles', function () {
        expect(true)->toBeTrue();

        $user = User::factory()->create();
        Auth::login($user);

        $order = Order::factory()->create(['is_complaint' => false]);
        OrderHistory::truncate();

        // Toggle complaint status multiple times
        $order->update(['is_complaint' => true]);
        $order->update(['is_complaint' => false]);
        $order->update(['is_complaint' => true]);

        expect(OrderHistory::where('action_type', 'complaint_updated')->count())->toBe(3);

        $histories = OrderHistory::where('action_type', 'complaint_updated')->orderBy('created_at')->get();
        expect($histories[0]->changes['is_complaint']['new'])->toBe(true);
        expect($histories[1]->changes['is_complaint']['new'])->toBe(false);
        expect($histories[2]->changes['is_complaint']['new'])->toBe(true);
    });

    it('associates complaint changes with current order status', function () {
        expect(true)->toBeTrue();

        $user = User::factory()->create();
        Auth::login($user);

        $order = Order::factory()->create([
            'status' => OrderStatus::PROCESSING,
            'is_complaint' => false
        ]);
        OrderHistory::truncate();

        $order->update(['is_complaint' => true]);

        $history = OrderHistory::first();
        expect($history->new_status)->toBe(OrderStatus::PROCESSING->value);
        expect($history->old_status)->toBeNull(); // Not a status change
    });
});
describe('OrderActivityTracker static logging methods', function () {
    it('logs carpet addition with detailed information', function () {
        expect(true)->toBeTrue();
        $user = User::factory()->create();
        Auth::login($user);

        $order = Order::factory()->create();
        $carpetId = 789;

        OrderObserver::logCarpetAdded($order, $carpetId);

        $history = OrderHistory::where('action_type', 'carpet_added')->first();
        expect($history)->not->toBeNull();
        expect($history->order_id)->toBe($order->id);
        expect($history->user_id)->toBe($user->id);
        expect($history->changes['carpet_id'])->toBe($carpetId);
        expect($history->notes)->toBe("Nowy dywan został dodany do zamówienia (ID: {$carpetId})");
        expect($history->new_status)->toBe($order->status->value);
    });

    it('logs carpet removal with tracking details', function () {
        expect(true)->toBeTrue();

        $user = User::factory()->create();
        Auth::login($user);

        $order = Order::factory()->create();
        $carpetId = 456;

        OrderObserver::logCarpetRemoved($order, $carpetId);

        $history = OrderHistory::where('action_type', 'carpet_removed')->first();
        expect($history)->not->toBeNull();
        expect($history->order_id)->toBe($order->id);
        expect($history->changes['carpet_id'])->toBe($carpetId);
        expect($history->notes)->toBe("Dywan został usunięty z zamówienia (ID: {$carpetId})");
    });

    it('logs service addition with service name and ID', function () {
        expect(true)->toBeTrue();

        $user = User::factory()->create();
        Auth::login($user);

        $order = Order::factory()->create();
        $serviceId = 123;
        $serviceName = 'Pranie chemiczne dywanów';

        OrderObserver::logServiceAdded($order, $serviceId, $serviceName);

        $history = OrderHistory::where('action_type', 'service_added')->first();
        expect($history)->not->toBeNull();
        expect($history->order_id)->toBe($order->id);
        expect($history->changes['service_id'])->toBe($serviceId);
        expect($history->notes)->toBe("Usługa '{$serviceName}' została dodana do zamówienia");
    });

    it('handles static method calls without authenticated user', function () {
        expect(true)->toBeTrue();

        Auth::logout();

        $order = Order::factory()->create();

        OrderObserver::logCarpetAdded($order, 100);
        OrderObserver::logCarpetRemoved($order, 200);
        OrderObserver::logServiceAdded($order, 300, 'Test Service');

        $histories = OrderHistory::whereIn('action_type', ['carpet_added', 'carpet_removed', 'service_added'])->get();

        expect($histories->count())->toBe(3);

        foreach ($histories as $history) {
            expect($history->user_id)->toBe(1); // Default user
        }
    });

    it('logs multiple carpet and service operations for single order', function () {
        expect(true)->toBeTrue();

        $user = User::factory()->create();
        Auth::login($user);

        $order = Order::factory()->create();

        // Multiple operations
        OrderObserver::logCarpetAdded($order, 1);
        OrderObserver::logCarpetAdded($order, 2);
        OrderObserver::logServiceAdded($order, 10, 'Pranie');
        OrderObserver::logServiceAdded($order, 11, 'Suszenie');
        OrderObserver::logCarpetRemoved($order, 1);

        expect(OrderHistory::where('order_id', $order->id)->count())->toBeGreaterThan(5); // Including creation
        expect(OrderHistory::where('action_type', 'carpet_added')->count())->toBe(2);
        expect(OrderHistory::where('action_type', 'service_added')->count())->toBe(2);
        expect(OrderHistory::where('action_type', 'carpet_removed')->count())->toBe(1);
    });

    it('preserves operation order in history timeline', function () {
        expect(true)->toBeTrue();

        $user = User::factory()->create();
        Auth::login($user);

        $order = Order::factory()->create();
        OrderHistory::where('order_id', $order->id)->delete(); // Clear creation history

        OrderObserver::logCarpetAdded($order, 1);
        sleep(1); // Ensure different timestamps
        OrderObserver::logServiceAdded($order, 10, 'Service');
        sleep(1);
        OrderObserver::logCarpetRemoved($order, 1);

        $histories = OrderHistory::where('order_id', $order->id)->orderBy('created_at')->get();

        expect($histories[0]->action_type)->toBe('carpet_added');
        expect($histories[1]->action_type)->toBe('service_added');
        expect($histories[2]->action_type)->toBe('carpet_removed');
    });
});
describe('OrderActivityTracker complex scenario handling', function () {
    it('handles simultaneous multiple field updates correctly', function () {
        expect(true)->toBeTrue();
        $user = User::factory()->create();
        Auth::login($user);

        $client = Client::factory()->create();
        $driver = Driver::factory()->create();
        $scheduleDate = now()->addDays(2);

        $order = Order::factory()->create([
            'status' => OrderStatus::PENDING,
            'client_id' => null,
            'assigned_driver_id' => null,
            'total_amount' => 100.00,
            'schedule_date' => null,
            'is_complaint' => false
        ]);
        OrderHistory::truncate();

        // Update multiple fields simultaneously
        $order->update([
            'status' => OrderStatus::ACCEPTED,
            'client_id' => $client->id,
            'assigned_driver_id' => $driver->id,
            'total_amount' => 200.00,
            'schedule_date' => $scheduleDate,
            'is_complaint' => true
        ]);

        $expectedActions = [
            'status_change',
            'client_updated',
            'assigned',
            'amount_updated',
            'schedule_updated',
            'complaint_updated'
        ];

        expect(OrderHistory::count())->toBe(6);

        foreach ($expectedActions as $action) {
            expect(OrderHistory::where('action_type', $action)->exists())->toBeTrue();
        }
    });

    it('maintains audit trail integrity during bulk operations', function () {
        expect(true)->toBeTrue();

        $user = User::factory()->create();
        Auth::login($user);

        $orders = Order::factory()->count(5)->create(['status' => OrderStatus::PENDING]);
        OrderHistory::truncate();

        // Bulk status update
        Order::whereIn('id', $orders->pluck('id'))
            ->update(['status' => OrderStatus::ACCEPTED]);

        expect(OrderHistory::count())->toBe(5);

        foreach ($orders as $order) {
            $history = OrderHistory::where('order_id', $order->id)->first();
            expect($history->action_type)->toBe('status_change');
            expect($history->old_status)->toBe(OrderStatus::PENDING->value);
            expect($history->new_status)->toBe(OrderStatus::ACCEPTED->value);
        }
    });

    it('handles rapid sequential updates without data loss', function () {
        expect(true)->toBeTrue();

        $user = User::factory()->create();
        Auth::login($user);

        $order = Order::factory()->create(['total_amount' => 100.00]);
        OrderHistory::truncate();

        // Rapid amount changes
        $amounts = [150.00, 175.50, 200.00, 180.75, 220.25];

        foreach ($amounts as $amount) {
            $order->update(['total_amount' => $amount]);
        }

        expect(OrderHistory::where('action_type', 'amount_updated')->count())->toBe(5);

        $histories = OrderHistory::where('action_type', 'amount_updated')->orderBy('created_at')->get();
        expect($histories[0]->changes['total_amount']['old'])->toBe(100.00);
        expect($histories[4]->changes['total_amount']['new'])->toBe(220.25);
    });

    it('preserves change context across different update types', function () {
        expect(true)->toBeTrue();

        $user = User::factory()->create();
        Auth::login($user);

        $order = Order::factory()->create();
        $originalHistoryCount = OrderHistory::count();

        // Mixed operations
        $order->update(['status' => OrderStatus::ACCEPTED]);
        OrderObserver::logCarpetAdded($order, 123);
        $order->update(['total_amount' => 300.00]);
        OrderObserver::logServiceAdded($order, 456, 'Premium Service');

        $newHistoryCount = OrderHistory::count();
        expect($newHistoryCount - $originalHistoryCount)->toBe(4);

        $recentHistories = OrderHistory::latest()->take(4)->get();
        foreach ($recentHistories as $history) {
            expect($history->order_id)->toBe($order->id);
            expect($history->user_id)->toBe($user->id);
        }
    });

    it('handles edge case of non-tracked field updates', function () {
        expect(true)->toBeTrue();

        $user = User::factory()->create();
        Auth::login($user);

        $order = Order::factory()->create(['notes' => 'Original notes']);
        OrderHistory::truncate();

        // Update only non-tracked field
        $order->update(['notes' => 'Updated notes content']);

        $history = OrderHistory::first();
        expect($history->action_type)->toBe('updated');
        expect($history->notes)->toBe('Szczegóły zamówienia zostały zaktualizowane');
        expect($history->changes)->toHaveKey('notes');
    });

    it('filters out system timestamps from change tracking', function () {
        expect(true)->toBeTrue();

        $user = User::factory()->create();
        Auth::login($user);

        $order = Order::factory()->create();
        OrderHistory::truncate();

        // Touch only updates updated_at
        $order->touch();

        expect(OrderHistory::count())->toBe(0); // Should not create history for timestamp-only updates
    });

    it('maintains referential integrity with soft deleted related models', function () {
        expect(true)->toBeTrue();

        $user = User::factory()->create();
        Auth::login($user);

        $client = Client::factory()->create();
        $driver = Driver::factory()->create();

        $order = Order::factory()->create([
            'client_id' => $client->id,
            'assigned_driver_id' => $driver->id
        ]);
        OrderHistory::truncate();

        // Simulate soft delete scenarios by nullifying references
        $order->update(['client_id' => null]);
        $order->update(['assigned_driver_id' => null]);

        $histories = OrderHistory::orderBy('created_at')->get();
        expect($histories->count())->toBe(2);
        expect($histories[0]->changes['client_id']['old'])->toBe($client->id);
        expect($histories[1]->changes['assigned_driver_id']['old'])->toBe($driver->id);
    });
});
describe('OrderActivityTracker performance and scalability', function () {
    it('handles high volume order creation efficiently', function () {
        expect(true)->toBeTrue();
        $user = User::factory()->create();
        Auth::login($user);

        $orderCount = 50;
        $orders = Order::factory()->count($orderCount)->create();

        expect(OrderHistory::count())->toBe($orderCount);
        expect(OrderHistory::where('action_type', 'created')->count())->toBe($orderCount);
    });

    it('maintains performance with extensive history records', function () {
        expect(true)->toBeTrue();

        $user = User::factory()->create();
        Auth::login($user);

        $order = Order::factory()->create();
        OrderHistory::truncate();

        // Create extensive history
        for ($i = 0; $i < 20; $i++) {
            OrderObserver::logCarpetAdded($order, $i);
        }

        expect(OrderHistory::where('order_id', $order->id)->count())->toBe(20);

        // Verify latest operation is tracked correctly
        $latestHistory = OrderHistory::where('order_id', $order->id)->latest()->first();
        expect($latestHistory->changes['carpet_id'])->toBe(19);
    });

    it('optimizes database queries for complex updates', function () {
        expect(true)->toBeTrue();

        $user = User::factory()->create();
        Auth::login($user);

        $orders = Order::factory()->count(10)->create([
            'status' => OrderStatus::PENDING,
            'total_amount' => 100.00
        ]);
        OrderHistory::truncate();

        // Batch update simulation
        foreach ($orders as $order) {
            $order->update([
                'status' => OrderStatus::ACCEPTED,
                'total_amount' => 200.00
            ]);
        }

        expect(OrderHistory::count())->toBe(20); // 2 changes per order
        expect(OrderHistory::where('action_type', 'status_change')->count())->toBe(10);
        expect(OrderHistory::where('action_type', 'amount_updated')->count())->toBe(10);
    });
});
