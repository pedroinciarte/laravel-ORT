<?php

use App\Models\Task;
use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\deleteJson;

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('deletes a task for the authenticated user', function () {
    $user = User::factory()->create();
    $task = Task::factory()->create(['user_id' => $user->id]);

    actingAs($user)
        ->deleteJson("/api/tasks/{$task->id}")
        ->assertOk()
        ->assertJson([
            'message' => 'Task deleted successfully.',
        ]);

    expect(Task::find($task->id))->toBeNull();
});


it('returns 403 if trying to delete another user\'s task', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $task = Task::factory()->create([
        'user_id' => $otherUser->id,
    ]);

    actingAs($user)
        ->deleteJson("/api/tasks/{$task->id}")
        ->assertForbidden();
});

it('returns 401 if user is not authenticated when deleting a task', function () {
    $task = Task::factory()->create(['user_id' => User::factory()->create()->id]);

    deleteJson("/api/tasks/{$task->id}")
        ->assertUnauthorized();
});
