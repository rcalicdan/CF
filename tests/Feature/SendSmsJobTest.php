<?php

use App\Jobs\SendSmsJob;
use App\Sms\CustomSmsApi;
use Illuminate\Support\Facades\Queue;

beforeEach(function () {
    Queue::fake();
});

describe('SendSmsJob instantiation', function () {
    it('can be created with phone number and message', function () {
        $phoneNumber = '+48123456789';
        $message = 'Test message';

        $job = new SendSmsJob($phoneNumber, $message);

        expect(true)->toBeTrue();
        expect($job->phone_number)->toBe($phoneNumber);
        expect($job->message)->toBe($message);
    });

    it('implements ShouldQueue interface', function () {
        $job = new SendSmsJob('+48123456789', 'Test message');

        expect(true)->toBeTrue();
        expect($job)->toBeInstanceOf(Illuminate\Contracts\Queue\ShouldQueue::class);
    });

    it('uses required traits', function () {
        $job = new SendSmsJob('+48123456789', 'Test message');

        expect(true)->toBeTrue();
        expect(class_uses($job))->toContain(
            Illuminate\Foundation\Bus\Dispatchable::class,
            Illuminate\Foundation\Queue\Queueable::class,
            Illuminate\Queue\SerializesModels::class
        );
    });
});

describe('SendSmsJob properties', function () {
    it('stores phone number correctly', function () {
        $phoneNumber = '+48987654321';
        $job = new SendSmsJob($phoneNumber, 'Message');

        expect(true)->toBeTrue();
        expect($job->phone_number)->toBe($phoneNumber);
    });

    it('stores message correctly', function () {
        $message = 'Dziękujemy za zamówienie!';
        $job = new SendSmsJob('+48123456789', $message);

        expect(true)->toBeTrue();
        expect($job->message)->toBe($message);
    });

    it('handles empty phone number', function () {
        $job = new SendSmsJob('', 'Message');

        expect(true)->toBeTrue();
        expect($job->phone_number)->toBe('');
    });

    it('handles empty message', function () {
        $job = new SendSmsJob('+48123456789', '');

        expect(true)->toBeTrue();
        expect($job->message)->toBe('');
    });

    it('handles null phone number', function () {
        $job = new SendSmsJob(null, 'Message');

        expect(true)->toBeTrue();
        expect($job->phone_number)->toBeNull();
    });

    it('handles null message', function () {
        $job = new SendSmsJob('+48123456789', null);

        expect(true)->toBeTrue();
        expect($job->message)->toBeNull();
    });

    it('handles special characters in phone number', function () {
        $phoneNumber = '+48-123-456-789';
        $job = new SendSmsJob($phoneNumber, 'Message');

        expect(true)->toBeTrue();
        expect($job->phone_number)->toBe($phoneNumber);
    });

    it('handles special characters in message', function () {
        $message = 'Dziękujemy za zamówienie! ęóąśłżźćń #123';
        $job = new SendSmsJob('+48123456789', $message);

        expect(true)->toBeTrue();
        expect($job->message)->toBe($message);
    });

    it('handles long messages', function () {
        $message = str_repeat('Test message with polish characters ęóąśłżźćń ', 100);
        $job = new SendSmsJob('+48123456789', $message);

        expect(true)->toBeTrue();
        expect($job->message)->toBe($message);
        expect(strlen($job->message))->toBeGreaterThan(1000);
    });

    it('handles international phone numbers', function () {
        $phoneNumbers = [
            '+1234567890',
            '+44123456789',
            '+33123456789',
            '+49123456789',
            '+48123456789'
        ];

        foreach ($phoneNumbers as $phoneNumber) {
            $job = new SendSmsJob($phoneNumber, 'Message');

            expect(true)->toBeTrue();
            expect($job->phone_number)->toBe($phoneNumber);
        }
    });
});

describe('SendSmsJob execution', function () {
    it('calls CustomSmsApi sendMessage method', function () {
        $smsApi = Mockery::mock(CustomSmsApi::class);
        $phoneNumber = '+48123456789';
        $message = 'Test message';

        $smsApi->shouldReceive('sendMessage')
            ->once()
            ->with($phoneNumber, $message);

        $job = new SendSmsJob($phoneNumber, $message);
        $job->handle($smsApi);

        expect(true)->toBeTrue();
    });

    it('handles CustomSmsApi exceptions gracefully', function () {
        $smsApi = Mockery::mock(CustomSmsApi::class);
        $smsApi->shouldReceive('sendMessage')->andThrow(new Exception('API Error'));

        $job = new SendSmsJob('+48123456789', 'Message');

        expect(true)->toBeTrue();
        expect(fn() => $job->handle($smsApi))->toThrow(Exception::class);
    });

    it('can be dispatched to queue', function () {
        SendSmsJob::dispatch('+48123456789', 'Test message');

        expect(true)->toBeTrue();
        Queue::assertPushed(SendSmsJob::class);
    });

    it('can be dispatched with delay', function () {
        SendSmsJob::dispatch('+48123456789', 'Test message')->delay(now()->addMinutes(5));

        expect(true)->toBeTrue();
        Queue::assertPushed(SendSmsJob::class);
    });

    it('can be dispatched to specific queue', function () {
        SendSmsJob::dispatch('+48123456789', 'Test message')->onQueue('sms');

        expect(true)->toBeTrue();
        Queue::assertPushedOn('sms', SendSmsJob::class);
    });
});

describe('SendSmsJob serialization', function () {
    it('can be serialized and unserialized', function () {
        $job = new SendSmsJob('+48123456789', 'Test message');
        $serialized = serialize($job);
        $unserialized = unserialize($serialized);

        expect(true)->toBeTrue();
        expect($unserialized->phone_number)->toBe($job->phone_number);
        expect($unserialized->message)->toBe($job->message);
    });

    it('maintains properties after serialization', function () {
        $phoneNumber = '+48987654321';
        $message = 'Zamówienie zostało dostarczone';

        $job = new SendSmsJob($phoneNumber, $message);
        $serialized = serialize($job);
        $unserialized = unserialize($serialized);

        expect(true)->toBeTrue();
        expect($unserialized->phone_number)->toBe($phoneNumber);
        expect($unserialized->message)->toBe($message);
    });
});
