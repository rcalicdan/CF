<?php

use App\Livewire\ClientFeedback\SubmitFeedback;
use App\Models\ClientFeedback;
use Livewire\Livewire;

test('it can submit feedback successfully', function () {
    $feedbackData = [
        'name' => 'John Doe',
        'opinion' => 'Great service! Very satisfied with the experience.',
        'rating' => 4,
    ];

    Livewire::test(SubmitFeedback::class)
        ->set('name', $feedbackData['name'])
        ->set('opinion', $feedbackData['opinion'])
        ->set('rating', $feedbackData['rating'])
        ->call('submit');

    $this->assertDatabaseHas('client_feedbacks', [
        'name' => $feedbackData['name'],
        'opinion' => $feedbackData['opinion'],
        'rating' => $feedbackData['rating'],
        'is_featured' => false,
    ]);
});

test('it validates required fields', function () {
    Livewire::test(SubmitFeedback::class)
        ->set('name', '')
        ->set('opinion', '')
        ->set('rating', 0)
        ->call('submit')
        ->assertHasErrors(['name', 'opinion', 'rating']);
});

test('it sets submitted flag after successful submission', function () {
    Livewire::test(SubmitFeedback::class)
        ->set('name', 'Jane Smith')
        ->set('opinion', 'Excellent experience!')
        ->set('rating', 5)
        ->call('submit')
        ->assertSet('submitted', true);
});

test('it dispatches feedback submitted event for non five star ratings', function () {
    Livewire::test(SubmitFeedback::class)
        ->set('name', 'Mike Johnson')
        ->set('opinion', 'Good service, could be better.')
        ->set('rating', 4)
        ->call('submit')
        ->assertDispatched('feedback-submitted');
});

test('it dispatches redirect event for five star ratings', function () {
    config(['services.google_reviews.enabled' => true]);
    config(['services.google_reviews.redirect_on_five_stars' => true]);
    config(['services.google_reviews.review_url' => 'https://g.page/r/TEST123/review']);

    Livewire::test(SubmitFeedback::class)
        ->set('name', 'Sarah Williams')
        ->set('opinion', 'Absolutely amazing! Best service ever!')
        ->set('rating', 5)
        ->call('submit')
        ->assertDispatched('redirect-to-google');
});

test('it can reset form successfully', function () {
    $component = Livewire::test(SubmitFeedback::class)
        ->set('name', 'Test User')
        ->set('opinion', 'Test opinion')
        ->set('rating', 3)
        ->set('submitted', true);

    $component->call('resetForm');

    $component
        ->assertSet('name', '')
        ->assertSet('opinion', '')
        ->assertSet('rating', 0)
        ->assertSet('submitted', false);
});

test('it validates rating is between 1 and 5', function () {
    Livewire::test(SubmitFeedback::class)
        ->set('name', 'Test User')
        ->set('opinion', 'Test opinion')
        ->set('rating', 0)
        ->call('submit')
        ->assertHasErrors(['rating']);

    Livewire::test(SubmitFeedback::class)
        ->set('name', 'Test User')
        ->set('opinion', 'Test opinion')
        ->set('rating', 6)
        ->call('submit')
        ->assertHasErrors(['rating']);
});

test('it validates name max length', function () {
    $longName = str_repeat('a', 256);
    
    Livewire::test(SubmitFeedback::class)
        ->set('name', $longName)
        ->set('opinion', 'Test opinion')
        ->set('rating', 4)
        ->call('submit')
        ->assertHasErrors(['name']);
});

test('it validates opinion max length', function () {
    $longOpinion = str_repeat('a', 1001);
    
    Livewire::test(SubmitFeedback::class)
        ->set('name', 'Test User')
        ->set('opinion', $longOpinion)
        ->set('rating', 4)
        ->call('submit')
        ->assertHasErrors(['opinion']);
});

test('it creates feedback with is featured false by default', function () {
    Livewire::test(SubmitFeedback::class)
        ->set('name', 'Featured Test')
        ->set('opinion', 'This should not be featured by default')
        ->set('rating', 5)
        ->call('submit');

    $feedback = ClientFeedback::where('name', 'Featured Test')->first();
    expect($feedback)->not->toBeNull();
    expect($feedback->is_featured)->toBeFalse();
});