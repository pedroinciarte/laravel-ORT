<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use function Pest\Laravel\{postJson};

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
});

it('creates a task successfully for authenticated user', function () {
    $payload = [
        'title' => 'Estudiar Laravel',
        'description' => 'Ver video de relaciones en Eloquent',
        'due_date' => now()->addDays(3)->toDateString(),
    ];

    $response = $this->actingAs($this->user)
        ->postJson('/api/tasks', $payload);

    $response->assertCreated()
        ->assertJsonStructure([
            'message',
            'task' => ['id', 'title', 'description', 'due_date', 'state', 'user_id', 'created_at', 'updated_at'],
        ]);

    expect($response['task']['title'])->toBe('Estudiar Laravel');
    expect($response['task']['state'])->toBe('pending');
});

it('fails to create a task without authentication', function () {
    $payload = [
        'title' => 'Tarea sin login',
        'description' => 'Esto no debería funcionar',
        'due_date' => now()->addDay()->toDateString(),
    ];

    $response = postJson('/api/tasks', $payload);

    $response->assertUnauthorized();
});

it('fails to create a task with invalid data', function () {
    $payload = [
        'title' => '',
        'due_date' => 'fecha invalida',
    ];

    $response = $this->actingAs($this->user)
        ->postJson('/api/tasks', $payload);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['title', 'due_date']);
});
