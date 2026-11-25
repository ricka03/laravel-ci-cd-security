<?php

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;

uses(RefreshDatabase::class);

beforeEach(function () {
    Notification::fake();

    $this->user = User::factory()->create([
        'email' => 'reset@example.test',
        'password' => Hash::make('OldPassword123!'),
    ]);
});

test('password reset request does not expose account existence', function () {
    $known = $this->post('/forgot-password', [
        'email' => 'reset@example.test',
    ]);

    $unknown = $this->post('/forgot-password', [
        'email' => 'unknown@example.test',
    ]);

    $known->assertOk();
    $unknown->assertOk();

    expect($known->json('message'))
        ->toBe($unknown->json('message'));
});

test('valid reset token changes password', function () {
    $token = Password::broker()->createToken(
        $this->user
    );

    $response = $this->post('/reset-password', [
        'email' => $this->user->email,
        'token' => $token,
        'password' => 'NewSecurePassword123!',
        'password_confirmation' => 'NewSecurePassword123!',
    ]);

    $response->assertOk();

    expect(
        Hash::check(
            'NewSecurePassword123!',
            $this->user->fresh()->password
        )
    )->toBeTrue();
});

test('reset token cannot be reused', function () {
    $token = Password::broker()->createToken(
        $this->user
    );

    $data = [
        'email' => $this->user->email,
        'token' => $token,
        'password' => 'NewSecurePassword123!',
        'password_confirmation' => 'NewSecurePassword123!',
    ];

    $this->post('/reset-password', $data)
        ->assertOk();

    $this->post('/reset-password', $data)
        ->assertStatus(422);
});

test('invalid reset token is rejected', function () {
    $response = $this->post('/reset-password', [
        'email' => $this->user->email,
        'token' => 'invalid-token',
        'password' => 'NewSecurePassword123!',
        'password_confirmation' => 'NewSecurePassword123!',
    ]);

    $response->assertStatus(422);

    expect(
        Hash::check(
            'OldPassword123!',
            $this->user->fresh()->password
        )
    )->toBeTrue();
});

