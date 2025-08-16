<?php

use App\Livewire\ProcessingCosts\CreatePage;
use App\Livewire\ProcessingCosts\Table;
use App\Livewire\ProcessingCosts\UpdatePage;
use App\Models\ProcessingCost;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\get;

uses(RefreshDatabase::class);

beforeEach(function () {
    $user = User::factory()->create();
    actingAs($user);
});

it('renders the processing costs table page', function () {
    Livewire::test(Table::class)
        ->assertOk();
});

it('can create a processing cost', function () {
    Livewire::test(CreatePage::class)
        ->set('name', 'Test Cost')
        ->set('type', 'energy')
        ->set('amount', 100)
        ->set('cost_date', now()->format('Y-m-d'))
        ->call('save')
        ->assertHasNoErrors()
        ->assertRedirect(route('processing-costs.index'));

    assertDatabaseHas('processing_costs', [
        'name' => 'Test Cost',
        'amount' => 100,
    ]);
});

it('shows validation errors on create page', function () {
    Livewire::test(CreatePage::class)
        ->set('name', '')
        ->set('type', '')
        ->set('amount', '')
        ->set('cost_date', '')
        ->call('save')
        ->assertHasErrors(['name', 'type', 'amount', 'cost_date']);
});

it('renders the update page', function () {
    $cost = ProcessingCost::factory()->create();

    Livewire::test(UpdatePage::class, ['processingCost' => $cost])
        ->assertOk()
        ->assertSet('name', $cost->name);
});

it('can update a processing cost', function () {
    $cost = ProcessingCost::factory()->create();

    Livewire::test(UpdatePage::class, ['processingCost' => $cost])
        ->set('name', 'Updated Cost Name')
        ->set('amount', 250)
        ->call('update')
        ->assertHasNoErrors()
        ->assertRedirect(route('processing-costs.index'));

    assertDatabaseHas('processing_costs', [
        'id' => $cost->id,
        'name' => 'Updated Cost Name',
        'amount' => 250,
    ]);
});

it('can delete a processing cost from the table', function () {
    $cost = ProcessingCost::factory()->create();

    Livewire::test(Table::class)
        ->call('deleteProcessingCost', $cost->id)
        ->assertDispatched('show-message');

    $this->assertDatabaseMissing('processing_costs', [
        'id' => $cost->id,
    ]);
});

it('can bulk delete processing costs from the table', function () {
    $costs = ProcessingCost::factory()->count(3)->create();
    $costIds = $costs->pluck('id')->toArray();

    Livewire::test(Table::class)
        ->set('selectedRows', $costIds)
        ->call('bulkDelete')
        ->assertDispatched('show-message');

    foreach ($costs as $cost) {
        $this->assertDatabaseMissing('processing_costs', [
            'id' => $cost->id,
        ]);
    }
});

