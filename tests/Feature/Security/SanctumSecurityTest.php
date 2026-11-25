<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
});

test('unauthenticated API request is rejected', function () {
    $this->getJson('/api/secure-products')
        ->assertUnauthorized();
});

test('read token can access read endpoint', function () {
    $token = $this->user->createToken(
        'read-token', ['products:read']
    )->plainTextToken;

    $this->withToken($token)
        ->getJson('/api/secure-products')
        ->assertOk();
});

test('read token cannot modify products', function () {
    $token = $this->user->createToken(
        'read-token', ['products:read']
    )->plainTextToken;

    $this->withToken($token)
        ->postJson('/api/secure-products', [
            'name' => 'Unauthorized Product',
        ])
        ->assertForbidden();
});

test('management token can access write endpoint', function () {
    $token = $this->user->createToken(
        'manage-token', ['products:read', 'products:write']
    )->plainTextToken;

    $this->withToken($token)
        ->postJson('/api/secure-products', [
            'name' => 'Authorized Product',
        ])
        ->assertCreated();
});

test('revoked token cannot access API', function () {
    $token = $this->user->createToken(
        'temporary-token', ['products:read']
    );

    $plainTextToken = $token->plainTextToken;
    $token->accessToken->delete();

    $this->withToken($plainTextToken)
        ->getJson('/api/secure-products')
        ->assertUnauthorized();
});

test('expired token cannot access API', function () {
    $token = $this->user->createToken(
        'expired-token', ['products:read'], now()->subMinute()
    );

    $this->withToken($token->plainTextToken)
        ->getJson('/api/secure-products')
        ->assertUnauthorized();
});
