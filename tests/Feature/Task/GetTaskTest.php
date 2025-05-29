<?php

use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use function Pest\Laravel\{ getJson , actingAs };

uses(RefreshDatabase::class);

it('shows the details of a task for the authenticated user', function () {
    $user = User::factory()->create();
    $task = Task::factory()->for($user)->create([
        'title' => 'Sample Task',
        'description' => 'This is a test description.',
        'state' => 'pending',
        'due_date' => now()->addDays(5)->toDateString(),
    ]);

    $response = actingAs($user)->getJson("/api/tasks/{$task->id}");

    $response->assertOk()
        ->assertJson([
            'task' => [
                'id' => $task->id,
                'title' => 'Sample Task',
                'description' => 'This is a test description.',
                'state' => 'pending',
                'due_date' => $task->due_date,
            ]
        ]);
});

it('returns 403 if the task does not belong to the user', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();

    $task = Task::factory()->for($user1)->create();

    $response = actingAs($user2)->getJson("/api/tasks/{$task->id}");

    $response->assertStatus(403)
        ->assertJson([
            'message' => 'Unauthorized.',
        ]);
});

it('returns 401 if the user is not authenticated', function () {
    $user = User::factory()->create();

    $task = Task::factory()->create([
        'user_id' => $user->id,
    ]);

    getJson("/api/tasks/{$task->id}")
        ->assertStatus(401);
});

