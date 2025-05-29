<?php

use App\Models\Task;
use App\Models\User;

use function Pest\Laravel\actingAs;
use Illuminate\Foundation\Testing\RefreshDatabase;
use function Pest\Laravel\getJson;

uses(RefreshDatabase::class);

it('filters tasks by pending state', function () {
    $user = User::factory()->create();

    Task::factory()->count(2)->for($user)->create(['state' => 'pending']);
    Task::factory()->count(1)->for($user)->create(['state' => 'completed']);

    actingAs($user)
        ->getJson('/api/tasks?state=pending')
        ->assertOk()
        ->assertJsonCount(2, 'tasks')
        ->assertJsonFragment(['state' => 'pending']);
});

it('filters tasks by completed state', function () {
    $user = User::factory()->create();

    Task::factory()->count(3)->for($user)->create(['state' => 'completed']);
    Task::factory()->count(2)->for($user)->create(['state' => 'pending']);

    actingAs($user)
        ->getJson('/api/tasks?state=completed')
        ->assertOk()
        ->assertJsonCount(3, 'tasks')
        ->assertJsonFragment(['state' => 'completed']);
});

it('returns all tasks if no state is provided', function () {
    $user = User::factory()->create();

    Task::factory()->count(2)->for($user)->create(['state' => 'pending']);
    Task::factory()->count(3)->for($user)->create(['state' => 'completed']);

    actingAs($user)
        ->getJson('/api/tasks')
        ->assertOk()
        ->assertJsonCount(5, 'tasks');
});

it('denies access to unauthenticated users when filtering', function () {
    $response = getJson('/api/tasks?state=pending');

    $response->assertUnauthorized(); // o ->assertStatus(401);
});
