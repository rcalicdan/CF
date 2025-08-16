<?php

use App\Enums\ComplaintStatus;
use App\Models\Complaint;
use App\Models\OrderCarpet;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\Passport;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();

    Passport::actingAs($this->user);

    $this->orderCarpet = OrderCarpet::factory()->create();
    $this->complaint = Complaint::create([
        'order_carpet_id' => $this->orderCarpet->id,
        'complaint_details' => 'Test complaint',
        'status' => ComplaintStatus::OPEN->value,
    ]);
});

test('can create a new complaint', function () {
    $response = $this->actingAs($this->user)
        ->postJson("/api/complaints/{$this->orderCarpet->id}/store-complaint", [
            'complaint_details' => 'New test complaint',
        ]);

    $response->assertStatus(200)
        ->assertJson([
            'status' => 'success',
            'message' => 'Complaint has been successfully submitted',
        ]);

    $this->assertDatabaseHas('complaints', [
        'order_carpet_id' => $this->orderCarpet->id,
        'complaint_details' => 'New test complaint',
        'status' => ComplaintStatus::OPEN->value,
    ]);
});

test('can update an existing complaint', function () {
    $response = $this->actingAs($this->user)
        ->putJson("/api/complaints/{$this->complaint->id}", [
            'complaint_details' => 'Updated complaint details',
            'status' => ComplaintStatus::IN_PROGRESS->value,
        ]);

    $response->assertStatus(200)
        ->assertJson([
            'status' => 'success',
            'message' => 'Complaint has been successfully updated',
        ]);

    $this->assertDatabaseHas('complaints', [
        'id' => $this->complaint->id,
        'complaint_details' => 'Updated complaint details',
        'status' => ComplaintStatus::IN_PROGRESS->value,
    ]);
});

test('can delete a complaint', function () {
    $response = $this->actingAs($this->user)
        ->deleteJson("/api/complaints/{$this->complaint->id}");

    $response->assertStatus(200)
        ->assertJson([
            'status' => 'success',
            'message' => 'Complaint has been successfully deleted',
        ]);

    $this->assertDatabaseMissing('complaints', [
        'id' => $this->complaint->id,
    ]);
});

test('cannot update complaint with invalid status', function () {
    $response = $this->actingAs($this->user)
        ->putJson("/api/complaints/{$this->complaint->id}", [
            'status' => 'invalid-status',
        ]);

    $response->assertStatus(422);
});

test('cannot create complaint without details', function () {
    $response = $this->actingAs($this->user)
        ->postJson("/api/complaints/{$this->orderCarpet->id}/store-complaint", [
            'complaint_details' => '',
        ]);

    $response->assertStatus(422);
});
