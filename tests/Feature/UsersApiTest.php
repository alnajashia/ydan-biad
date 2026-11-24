<?php

use App\Features\Customers\Infrastructure\Persistence\Models\Customer;
use App\Features\Users\Infrastructure\Persistence\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

function registerPayload(array $overrides = []): array
{
    return array_merge([
        'full_name' => 'Ahmed Test',
        'phone' => '777123456',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'gender' => 'male',
        'identity_type' => 'national_id',
        'identity_number' => '1234567890',
        'identity_front_image' => UploadedFile::fake()->image('front.jpg'),
        'identity_back_image' => UploadedFile::fake()->image('back.jpg'),
        'identity_expiry_date' => '2030-01-01',
    ], $overrides);
}

test('a user can register with a phone number and waits for activation', function () {
    Storage::fake('public');

    $response = $this->post('/api/register', registerPayload());

    $response->assertCreated()
        ->assertJsonPath('user.phone', '777123456')
        ->assertJsonPath('user.status', 'suspended')
        ->assertJsonPath('user.identity_type', 'national_id')
        ->assertJsonStructure(['user' => ['id', 'full_name', 'phone'], 'message'])
        ->assertJsonMissing(['token']);

    $this->assertDatabaseHas('users', ['phone' => '777123456', 'status' => 'suspended']);
});

test('registration rejects a duplicate phone number', function () {
    Storage::fake('public');
    User::factory()->create(['phone' => '777123456']);

    $this->post('/api/register', registerPayload())
        ->assertUnprocessable()->assertJsonValidationErrors('phone');
});

test('registration rejects an unsupported identity type', function () {
    Storage::fake('public');

    $this->post('/api/register', registerPayload(['identity_type' => 'driving_license']))
        ->assertUnprocessable()->assertJsonValidationErrors('identity_type');
});

test('registration cannot grant an admin role or an active status', function () {
    Storage::fake('public');

    $this->post('/api/register', registerPayload(['role' => 'admin', 'status' => 'active']))
        ->assertCreated();

    $this->assertDatabaseHas('users', ['phone' => '777123456', 'role' => 'customer', 'status' => 'suspended']);
});

test('a user can login with phone and password', function () {
    User::factory()->create(['phone' => '777123456']);

    $this->postJson('/api/login', [
        'phone' => '777123456',
        'password' => 'password',
    ])->assertOk()->assertJsonStructure(['user', 'access_token', 'refresh_token', 'token_type', 'expires_in']);
});

test('login fails with a wrong password', function () {
    User::factory()->create(['phone' => '777123456']);

    $this->postJson('/api/login', [
        'phone' => '777123456',
        'password' => 'wrong-password',
    ])->assertUnprocessable();
});

test('login fails for a suspended account', function () {
    User::factory()->suspended()->create(['phone' => '777123456']);

    $this->postJson('/api/login', [
        'phone' => '777123456',
        'password' => 'password',
    ])->assertUnprocessable()->assertJsonValidationErrors('phone')
        ->assertJsonPath('errors.phone.0', 'This account is not active.');
});

test('users endpoints require authentication', function () {
    $this->getJson('/api/users')->assertUnauthorized();
    $this->getJson('/api/customers')->assertUnauthorized();
    $this->getJson('/api/me')->assertUnauthorized();
});

test('a non-admin user cannot manage users', function () {
    Sanctum::actingAs(User::factory()->create());

    $this->getJson('/api/users')->assertForbidden();
});

test('an authenticated admin can manage users and customers', function () {
    Storage::fake('public');
    Sanctum::actingAs(User::factory()->admin()->create());

    $created = $this->post('/api/users', [
        'full_name' => 'Managed User',
        'phone' => '777999888',
        'password' => 'password123',
        'gender' => 'male',
        'identity_type' => 'national_id',
        'identity_number' => '9876543210',
        'identity_front_image' => UploadedFile::fake()->image('front.jpg'),
        'identity_back_image' => UploadedFile::fake()->image('back.jpg'),
        'identity_expiry_date' => '2030-01-01',
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
    Sanctum::actingAs(User::factory()->admin()->create());
    $customer = Customer::factory()->create();

    $this->postJson('/api/customers', [
        'user_id' => $customer->user_id,
    ])->assertUnprocessable()->assertJsonValidationErrors('user_id');
});
