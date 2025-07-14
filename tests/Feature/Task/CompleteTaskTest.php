<?php

use App\Models\Task;
use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\patchJson;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('marks a task as completed for the authenticated user', function () {
    $user = User::factory()->create();
    $task = Task::factory()->create([
        'user_id' => $user->id,
        'state' => 'pending',
    ]);

    actingAs($user)
        ->patchJson("/api/tasks/{$task->id}/complete")
        ->assertOk()
        ->assertJsonFragment([
            'state' => 'completed',
        ]);
});

it('returns 403 if trying to complete another user\'s task', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $task = Task::factory()->create([
        'user_id' => $otherUser->id,
        'state' => 'pending',
    ]);

    actingAs($user)
        ->patchJson("/api/tasks/{$task->id}/complete")
        ->assertForbidden();
});

it('returns 401 if user is not authenticated when completing a task', function () {
    $task = Task::factory()->create(['user_id' => User::factory()->create()->id]);

    auth()->logout();

    patchJson("/api/tasks/{$task->id}/complete")
        ->assertUnauthorized();
});
