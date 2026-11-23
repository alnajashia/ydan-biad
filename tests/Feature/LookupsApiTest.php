<?php

use App\Features\Lookups\Infrastructure\Persistence\Models\Role;
use App\Features\Lookups\Infrastructure\Persistence\Models\Status;
use App\Features\Users\Infrastructure\Persistence\Models\User;
use Database\Seeders\RoleSeeder;
use Database\Seeders\StatusSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed([StatusSeeder::class, RoleSeeder::class]);
});

test('statuses and roles endpoints require authentication', function () {
    $this->getJson('/api/statuses')->assertUnauthorized();
    $this->getJson('/api/roles')->assertUnauthorized();
});

test('an authenticated user can manage statuses', function () {
    Sanctum::actingAs(User::factory()->create());

    $created = $this->postJson('/api/statuses', [
        'name' => 'pending',
        'value' => true,
    ])->assertCreated()
        ->assertJsonPath('status.name', 'pending')
        ->assertJsonPath('status.value', true)
        ->json('status');

    $this->getJson('/api/statuses')->assertOk()
        ->assertJsonPath('data.0.name', 'active');

    $this->getJson("/api/statuses/{$created['id']}")->assertOk()
        ->assertJsonPath('status.name', 'pending');

    $this->putJson("/api/statuses/{$created['id']}", [
        'value' => false,
    ])->assertOk()->assertJsonPath('status.value', false);

    $this->deleteJson("/api/statuses/{$created['id']}")->assertOk();

    $this->assertDatabaseMissing('statuses', ['id' => $created['id']]);
});

test('creating a status with a duplicate name is rejected', function () {
    Sanctum::actingAs(User::factory()->create());

    $this->postJson('/api/statuses', [
        'name' => 'active',
        'value' => true,
    ])->assertUnprocessable()->assertJsonValidationErrors('name');
});

test('an authenticated user can manage roles', function () {
    Sanctum::actingAs(User::factory()->create());

    $created = $this->postJson('/api/roles', [
        'name' => 'manager',
        'value' => true,
    ])->assertCreated()
        ->assertJsonPath('role.name', 'manager')
        ->json('role');

    $this->getJson('/api/roles')->assertOk()
        ->assertJsonPath('data.0.name', 'admin');

    $this->getJson("/api/roles/{$created['id']}")->assertOk()
        ->assertJsonPath('role.name', 'manager');

    $this->putJson("/api/roles/{$created['id']}", [
        'name' => 'supervisor',
    ])->assertOk()->assertJsonPath('role.name', 'supervisor');

    $this->deleteJson("/api/roles/{$created['id']}")->assertOk();

    $this->assertDatabaseMissing('roles', ['id' => $created['id']]);
});

test('creating a role with a duplicate name is rejected', function () {
    Sanctum::actingAs(User::factory()->create());

    $this->postJson('/api/roles', [
        'name' => 'admin',
        'value' => true,
    ])->assertUnprocessable()->assertJsonValidationErrors('name');
});

test('a user with an inactive status cannot login', function () {
    $inactive = Status::query()->where('name', 'inactive')->firstOrFail();

    User::factory()->create([
        'phone' => '777123456',
        'status_id' => $inactive->id,
    ]);

    $this->postJson('/api/login', [
        'phone' => '777123456',
        'password' => 'password',
    ])->assertUnprocessable()->assertJsonValidationErrors('phone');
});

test('a user whose active status has value false cannot login', function () {
    $active = Status::query()->where('name', 'active')->firstOrFail();
    $active->update(['value' => false]);

    User::factory()->create([
        'phone' => '777123456',
        'status_id' => $active->id,
    ]);

    $this->postJson('/api/login', [
        'phone' => '777123456',
        'password' => 'password',
    ])->assertUnprocessable()->assertJsonValidationErrors('phone');
});

test('a user can be assigned a status and a role by id', function () {
    Sanctum::actingAs(User::factory()->create());

    $suspend = Status::query()->where('name', 'suspend')->firstOrFail();
    $employee = Role::query()->where('name', 'employee')->firstOrFail();

    $this->postJson('/api/users', [
        'full_name' => 'Assigned User',
        'phone' => '777555444',
        'password' => 'password123',
        'status_id' => $suspend->id,
        'role_id' => $employee->id,
    ])->assertCreated()
        ->assertJsonPath('user.status.name', 'suspend')
        ->assertJsonPath('user.role.name', 'employee');

    $this->assertDatabaseHas('users', [
        'phone' => '777555444',
        'status_id' => $suspend->id,
        'role_id' => $employee->id,
    ]);
});

test('assigning a non-existent status or role is rejected', function () {
    Sanctum::actingAs(User::factory()->create());

    $this->postJson('/api/users', [
        'full_name' => 'Invalid User',
        'phone' => '777555444',
        'password' => 'password123',
        'status_id' => 9999,
        'role_id' => 9999,
    ])->assertUnprocessable()->assertJsonValidationErrors(['status_id', 'role_id']);
});
