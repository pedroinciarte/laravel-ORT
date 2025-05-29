<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;

use function Pest\Laravel\postJson;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create([
        'email' => 'juan@example.com',
        'password' => Hash::make('password123'),
    ]);
});

it('logs in successfully with correct credentials', function () {
    $response = postJson('/api/login', [
        'email' => 'juan@example.com',
        'password' => 'password123',
    ]);

    $response->assertStatus(200)
             ->assertJsonStructure([
                 'message',
                 'token',
                 'user' => ['id', 'name', 'email'],
             ]);
});

it('fails login with incorrect password', function () {
    $response = postJson('/api/login', [
        'email' => 'juan@example.com',
        'password' => 'wrongpassword',
    ]);

    $response->assertStatus(422)
             ->assertJsonValidationErrors(['email']);
});

it('fails login with non-existing email', function () {
    $response = postJson('/api/login', [
        'email' => 'nope@example.com',
        'password' => 'whatever123',
    ]);

    $response->assertStatus(422)
             ->assertJsonValidationErrors(['email']);
});
