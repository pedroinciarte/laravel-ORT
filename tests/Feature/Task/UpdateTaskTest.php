<?php

use App\Models\Task;
use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\patchJson;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('updates a task for the authenticated user', function () {
    $user = User::factory()->create();
    $task = Task::factory()->for($user)->create();

    $payload = [
        'title' => 'Título actualizado',
        'description' => 'Descripción nueva',
        'due_date' => '2025-06-15',
        'state' => 'completed',
    ];

    actingAs($user)
        ->patchJson("/api/tasks/{$task->id}", $payload)
        ->assertOk()
        ->assertJsonFragment([
            'title' => $payload['title'],
            'description' => $payload['description'],
            'due_date' => $payload['due_date'],
            'state' => $payload['state'],
        ]);
});

it('returns 403 if trying to update another user\'s task', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $task = Task::factory()->for($otherUser)->create();

    actingAs($user)
        ->patchJson("/api/tasks/{$task->id}", [
            'title' => 'Intento inválido',
        ])
        ->assertForbidden();
});

it('returns 401 if user is not authenticated', function () {
    $task = Task::factory()->for(User::factory())->create();

    patchJson("/api/tasks/{$task->id}", [
        'title' => 'Título nuevo',
    ])
    ->assertUnauthorized();
});
