<?php

use App\Features\Users\Infrastructure\Persistence\Models\Customer;
use App\Features\Users\Infrastructure\Persistence\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

test('a user can register with a phone number and receives a token', function () {
    $response = $this->postJson('/api/register', [
        'full_name' => 'Ahmed Test',
        'phone' => '777123456',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'identity_type' => 'national_id',
        'identity_number' => '1234567890',
    ]);

    $response->assertCreated()
        ->assertJsonPath('user.phone', '777123456')
        ->assertJsonPath('user.identity_type', 'national_id')
        ->assertJsonStructure(['user' => ['id', 'full_name', 'phone'], 'token']);

    $this->assertDatabaseHas('users', ['phone' => '777123456']);
});

test('registration rejects a duplicate phone number', function () {
    User::factory()->create(['phone' => '777123456']);

    $this->postJson('/api/register', [
        'full_name' => 'Another User',
        'phone' => '777123456',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ])->assertUnprocessable()->assertJsonValidationErrors('phone');
});

test('registration rejects an unsupported identity type', function () {
    $this->postJson('/api/register', [
        'full_name' => 'Ahmed Test',
        'phone' => '777123456',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'identity_type' => 'driving_license',
    ])->assertUnprocessable()->assertJsonValidationErrors('identity_type');
});

test('a user can login with phone and password', function () {
    User::factory()->create(['phone' => '777123456']);

    $this->postJson('/api/login', [
        'phone' => '777123456',
        'password' => 'password',
    ])->assertOk()->assertJsonStructure(['user', 'token']);
});

test('login fails with a wrong password', function () {
    User::factory()->create(['phone' => '777123456']);

    $this->postJson('/api/login', [
        'phone' => '777123456',
        'password' => 'wrong-password',
    ])->assertUnprocessable();
});

test('users endpoints require authentication', function () {
    $this->getJson('/api/users')->assertUnauthorized();
    $this->getJson('/api/customers')->assertUnauthorized();
});

test('an authenticated user can manage users and customers', function () {
    Sanctum::actingAs(User::factory()->create());

    $created = $this->postJson('/api/users', [
        'full_name' => 'Managed User',
        'phone' => '777999888',
        'password' => 'password123',
        'role' => 'customer',
    ])->assertCreated()->json('user');

    $this->getJson('/api/users')->assertOk();
    $this->getJson("/api/users/{$created['id']}")->assertOk()
        ->assertJsonPath('user.phone', '777999888');

    $this->putJson("/api/users/{$created['id']}", [
        'full_name' => 'Updated Name',
    ])->assertOk()->assertJsonPath('user.full_name', 'Updated Name');

    $customer = $this->postJson('/api/customers', [
        'user_id' => $created['id'],
        'city' => 'Sanaa',
        'district' => 'Al-Sabeen',
        'latitude' => 15.3694,
        'longitude' => 44.1910,
    ])->assertCreated()->json('customer');

    $this->getJson("/api/customers/{$customer['id']}")->assertOk()
        ->assertJsonPath('customer.city', 'Sanaa');

    $this->deleteJson("/api/customers/{$customer['id']}")->assertOk();
    $this->deleteJson("/api/users/{$created['id']}")->assertOk();

    $this->assertDatabaseMissing('users', ['id' => $created['id']]);
    $this->assertDatabaseMissing('customers', ['id' => $customer['id']]);
});

test('a customer profile is unique per user', function () {
    Sanctum::actingAs(User::factory()->create());
    $customer = Customer::factory()->create();

    $this->postJson('/api/customers', [
        'user_id' => $customer->user_id,
    ])->assertUnprocessable()->assertJsonValidationErrors('user_id');
});
