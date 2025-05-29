<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('logs out successfully', function () {
    $user = User::factory()->create();

    $token = $user->createToken('api-token')->plainTextToken;

    $response = $this->withHeader('Authorization', "Bearer $token")
                     ->postJson('/api/logout');

    $response->assertStatus(200)
             ->assertJson([
                 'message' => 'Logged out successfully.'
             ]);
});
