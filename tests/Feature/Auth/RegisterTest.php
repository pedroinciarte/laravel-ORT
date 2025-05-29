<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Testing\Fluent\AssertableJson;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\postJson;
use function Pest\Laravel\assertDatabaseHas;

uses(RefreshDatabase::class);

it('registers a user successfully', function () {
    $payload = [
        'name' => 'Juan Pérez',
        'email' => 'juan@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ];

    postJson('/api/register', $payload)
        ->assertCreated()
        ->assertJson(fn (AssertableJson $json) =>
            $json->where('message', 'User created successfully.')
                 ->has('user.id')
                 ->where('user.name', 'Juan Pérez')
                 ->where('user.email', 'juan@example.com')
        );

    assertDatabaseHas('users', [
        'email' => 'juan@example.com',
    ]);
});

it('fails if password confirmation does not match', function () {
    $payload = [
        'name' => 'Juan Pérez',
        'email' => 'juan@example.com',
        'password' => 'password123',
        'password_confirmation' => 'another123',
    ];

    postJson('/api/register', $payload)
        ->assertStatus(422)
        ->assertJsonValidationErrors(['password']);
});

it('fails if email is already taken', function () {
    User::factory()->create([
        'email' => 'juan@example.com',
    ]);

    $payload = [
        'name' => 'Juan Pérez',
        'email' => 'juan@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ];

    postJson('/api/register', $payload)
        ->assertStatus(422)
        ->assertJsonValidationErrors(['email']);
});
