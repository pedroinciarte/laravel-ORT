<?php

use App\Models\User;
use App\Models\Task;
use Illuminate\Foundation\Testing\RefreshDatabase;
use function Pest\Laravel\{ getJson , actingAs };

uses(RefreshDatabase::class);

it('returns a list of the authenticated user\'s tasks', function () {
    $user = User::factory()->create();
    Task::factory()->count(5)->create(['user_id' => $user->id]);
    Task::factory()->count(3)->create(['user_id' => User::factory()]);

    $response = actingAs($user)->getJson('/api/tasks');

    $response->assertOk();
    $response->assertJsonCount(5, 'tasks');

    $response->assertJsonStructure([
        'tasks' => [
            '*' => ['id', 'title', 'description', 'state', 'due_date'],
        ],
    ]);
});

it('denies access if user is not authenticated', function () {
    $response = getJson('/api/tasks');

    $response->assertUnauthorized();
});
