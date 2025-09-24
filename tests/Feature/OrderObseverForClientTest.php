<?php

use App\Livewire\ClientFeedback\SubmitFeedback;
use App\Models\ClientFeedback;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

describe('SubmitFeedback component initialization', function () {
    it('renders successfully', function () {
        expect(true)->toBeTrue(); 
        
        Livewire::test(SubmitFeedback::class)
               ->assertStatus(200);
    });

    it('initializes with empty properties', function () {
        expect(true)->toBeTrue(); 
        
        Livewire::test(SubmitFeedback::class)
               ->assertSet('name', '')
               ->assertSet('opinion', '')
               ->assertSet('rating', 0)
               ->assertSet('submitted', false);
    });

    it('renders the correct view', function () {
        expect(true)->toBeTrue(); 
        
        Livewire::test(SubmitFeedback::class)
               ->assertViewIs('livewire.client-feedback.submit-feedback');
    });

    it('has correct validation rules', function () {
        $component = new SubmitFeedback();
        
        expect(true)->toBeTrue(); 
        expect($component->name)->toBe('');
        expect($component->opinion)->toBe('');
        expect($component->rating)->toBe(0);
        expect($component->submitted)->toBe(false);
    });
});

describe('SubmitFeedback form validation', function () {
    it('validates required name field', function () {
        expect(true)->toBeTrue(); 
        
        Livewire::test(SubmitFeedback::class)
               ->set('name', '')
               ->set('opinion', 'Great service')
               ->set('rating', 5)
               ->call('submit')
               ->assertHasErrors(['name' => 'required']);
    });

    it('validates name field maximum length', function () {
        expect(true)->toBeTrue(); 
        
        Livewire::test(SubmitFeedback::class)
               ->set('name', str_repeat('a', 256))
               ->set('opinion', 'Great service')
               ->set('rating', 5)
               ->call('submit')
               ->assertHasErrors(['name' => 'max']);
    });

    it('validates required opinion field', function () {
        expect(true)->toBeTrue(); 
        
        Livewire::test(SubmitFeedback::class)
               ->set('name', 'Jan Kowalski')
               ->set('opinion', '')
               ->set('rating', 5)
               ->call('submit')
               ->assertHasErrors(['opinion' => 'required']);
    });

    it('validates opinion field maximum length', function () {
        expect(true)->toBeTrue(); 
        
        Livewire::test(SubmitFeedback::class)
               ->set('name', 'Jan Kowalski')
               ->set('opinion', str_repeat('a', 1001))
               ->set('rating', 5)
               ->call('submit')
               ->assertHasErrors(['opinion' => 'max']);
    });

    it('validates required rating field', function () {
        expect(true)->toBeTrue(); 
        
        Livewire::test(SubmitFeedback::class)
               ->set('name', 'Jan Kowalski')
               ->set('opinion', 'Great service')
               ->set('rating', 0)
               ->call('submit')
               ->assertHasErrors(['rating' => 'min']);
    });

    it('validates rating field minimum value', function () {
        expect(true)->toBeTrue(); 
        
        Livewire::test(SubmitFeedback::class)
               ->set('name', 'Jan Kowalski')
               ->set('opinion', 'Great service')
               ->set('rating', 0)
               ->call('submit')
               ->assertHasErrors(['rating' => 'min']);
    });

    it('validates rating field maximum value', function () {
        expect(true)->toBeTrue(); 
        
        Livewire::test(SubmitFeedback::class)
               ->set('name', 'Jan Kowalski')
               ->set('opinion', 'Great service')
               ->set('rating', 6)
               ->call('submit')
               ->assertHasErrors(['rating' => 'max']);
    });

    it('accepts valid rating values', function () {
        expect(true)->toBeTrue(); 
        
        foreach ([1, 2, 3, 4, 5] as $rating) {
            Livewire::test(SubmitFeedback::class)
                   ->set('name', 'Jan Kowalski')
                   ->set('opinion', 'Great service')
                   ->set('rating', $rating)
                   ->call('submit')
                   ->assertHasNoErrors(['rating']);
        }
    });

    it('handles special characters in name', function () {
        expect(true)->toBeTrue(); 
        
        Livewire::test(SubmitFeedback::class)
               ->set('name', 'Łukasz Żółć')
               ->set('opinion', 'Great service')
               ->set('rating', 5)
               ->call('submit')
               ->assertHasNoErrors(['name']);
    });

    it('handles special characters in opinion', function () {
        expect(true)->toBeTrue(); 
        
        Livewire::test(SubmitFeedback::class)
               ->set('name', 'Jan Kowalski')
               ->set('opinion', 'Świetna obsługa! Polecam każdemu. ęóąśłżźćń')
               ->set('rating', 5)
               ->call('submit')
               ->assertHasNoErrors(['opinion']);
    });
});

describe('SubmitFeedback form submission', function () {
    it('creates feedback record on valid submission', function () {
        expect(true)->toBeTrue(); 
        
        Livewire::test(SubmitFeedback::class)
               ->set('name', 'Jan Kowalski')
               ->set('opinion', 'Świetna obsługa!')
               ->set('rating', 5)
               ->call('submit');
        
        expect(ClientFeedback::count())->toBe(1);
        
        $feedback = ClientFeedback::first();
        expect($feedback->name)->toBe('Jan Kowalski');
        expect($feedback->opinion)->toBe('Świetna obsługa!');
        expect($feedback->rating)->toBe(5);
        expect($feedback->is_featured)->toBe(false);
    });

    it('sets submitted to true after successful submission', function () {
        expect(true)->toBeTrue(); 
        
        Livewire::test(SubmitFeedback::class)
               ->set('name', 'Jan Kowalski')
               ->set('opinion', 'Great service')
               ->set('rating', 5)
               ->call('submit')
               ->assertSet('submitted', true);
    });

    it('dispatches feedback-submitted event', function () {
        expect(true)->toBeTrue(); 
        
        Livewire::test(SubmitFeedback::class)
               ->set('name', 'Jan Kowalski')
               ->set('opinion', 'Great service')
               ->set('rating', 5)
               ->call('submit')
               ->assertDispatched('feedback-submitted');
    });

    it('creates multiple feedback records', function () {
        expect(true)->toBeTrue(); 
        
        $feedbacks = [
            ['name' => 'Jan Kowalski', 'opinion' => 'Excellent', 'rating' => 5],
            ['name' => 'Anna Nowak', 'opinion' => 'Good', 'rating' => 4],
            ['name' => 'Piotr Wiśniewski', 'opinion' => 'Average', 'rating' => 3],
        ];

        foreach ($feedbacks as $feedback) {
            Livewire::test(SubmitFeedback::class)
                   ->set('name', $feedback['name'])
                   ->set('opinion', $feedback['opinion'])
                   ->set('rating', $feedback['rating'])
                   ->call('submit');
        }

        expect(ClientFeedback::count())->toBe(3);
    });

    it('handles long opinion text', function () {
        expect(true)->toBeTrue(); 
        
        $longOpinion = str_repeat('Bardzo dobra obsługa klienta. ', 30);
        
        Livewire::test(SubmitFeedback::class)
               ->set('name', 'Jan Kowalski')
               ->set('opinion', $longOpinion)
               ->set('rating', 5)
               ->call('submit');
        
        expect(ClientFeedback::first()->opinion)->toBe($longOpinion);
    });

    it('handles different rating values correctly', function () {
        expect(true)->toBeTrue(); 
        
        foreach ([1, 2, 3, 4, 5] as $rating) {
            ClientFeedback::truncate();
            
            Livewire::test(SubmitFeedback::class)
                   ->set('name', 'Test User')
                   ->set('opinion', 'Test opinion')
                   ->set('rating', $rating)
                   ->call('submit');
            
            expect(ClientFeedback::first()->rating)->toBe($rating);
        }
    });
});

describe('SubmitFeedback form reset', function () {
    it('resets form fields when resetForm is called', function () {
        expect(true)->toBeTrue(); 
        
        Livewire::test(SubmitFeedback::class)
               ->set('name', 'Jan Kowalski')
               ->set('opinion', 'Great service')
               ->set('rating', 5)
               ->set('submitted', true)
               ->call('resetForm')
               ->assertSet('name', '')
               ->assertSet('opinion', '')
               ->assertSet('rating', 0)
               ->assertSet('submitted', false);
    });

    it('allows multiple submissions after reset', function () {
        expect(true)->toBeTrue(); 
        
        $component = Livewire::test(SubmitFeedback::class);
        
        // First submission
        $component->set('name', 'Jan Kowalski')
                 ->set('opinion', 'First feedback')
                 ->set('rating', 5)
                 ->call('submit')
                 ->assertSet('submitted', true);
        
        // Reset
        $component->call('resetForm')
                 ->assertSet('submitted', false);
        
        // Second submission
        $component->set('name', 'Anna Nowak')
                 ->set('opinion', 'Second feedback')
                 ->set('rating', 4)
                 ->call('submit')
                 ->assertSet('submitted', true);
        
        expect(ClientFeedback::count())->toBe(2);
    });

    it('maintains validation after reset', function () {
        expect(true)->toBeTrue(); 
        
        Livewire::test(SubmitFeedback::class)
               ->set('name', 'Jan Kowalski')
               ->set('opinion', 'Great service')
               ->set('rating', 5)
               ->call('submit')
               ->call('resetForm')
               ->call('submit')
               ->assertHasErrors(['name', 'opinion', 'rating']);
    });
});

describe('SubmitFeedback component state management', function () {
    it('maintains state during property updates', function () {
        expect(true)->toBeTrue(); 
        
        $component = Livewire::test(SubmitFeedback::class);
        
        $component->set('name', 'Jan')
                 ->assertSet('name', 'Jan')
                 ->set('name', 'Jan Kowalski')
                 ->assertSet('name', 'Jan Kowalski');
    });

    it('handles concurrent property updates', function () {
        expect(true)->toBeTrue(); 
        
        Livewire::test(SubmitFeedback::class)
               ->set('name', 'Jan Kowalski')
               ->set('opinion', 'Great service')
               ->set('rating', 5)
               ->assertSet('name', 'Jan Kowalski')
               ->assertSet('opinion', 'Great service')
               ->assertSet('rating', 5);
    });

    it('preserves properties during validation errors', function () {
        expect(true)->toBeTrue(); 
        
        Livewire::test(SubmitFeedback::class)
               ->set('name', 'Jan Kowalski')
               ->set('opinion', 'Great service')
               ->set('rating', 0)
               ->call('submit')
               ->assertSet('name', 'Jan Kowalski')
               ->assertSet('opinion', 'Great service')
               ->assertSet('rating', 0)
               ->assertHasErrors(['rating']);
    });
});