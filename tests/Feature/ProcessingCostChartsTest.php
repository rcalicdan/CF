<?php

use App\Livewire\ProcessingCosts\Charts;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

it('renders successfully and loads initial data', function () {
    Livewire::test(Charts::class)
        ->assertStatus(200)
        ->assertViewIs('livewire.processing-costs.charts')
        ->assertProperty('weeklyData', [])
        ->assertProperty('monthlyData', [])
        ->assertProperty('yearlyData', [])
        ->assertProperty('costTypeData', []);
});

it('loads data on mount', function () {
    Livewire::test(Charts::class)
        ->assertStatus(200)
        ->assertNotEmitted('updateCharts');
});

it('updates data when active tab changes', function () {
    Livewire::test(Charts::class)
        ->set('activeTab', 'monthly')
        ->assertDispatched('updateCharts');
});

it('refreshes data and dispatches events', function () {
    Livewire::test(Charts::class)
        ->call('refreshData')
        ->assertDispatched('updateCharts')
        ->assertDispatched('notify');
});

it('returns arrays for all data fetching methods', function () {
    $component = new Charts();
    $component->mount();

    expect($component->weeklyData)->toBeArray();
    expect($component->monthlyData)->toBeArray();
    expect($component->yearlyData)->toBeArray();
    expect($component->costTypeData)->toBeArray();
});