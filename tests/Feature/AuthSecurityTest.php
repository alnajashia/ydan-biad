<?php

use App\Features\Users\Infrastructure\Persistence\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

function loginAs(string $phone = '777123456'): array
{
    $response = test()->postJson('/api/login', [
        'phone' => $phone,
        'password' => 'password',
    ])->assertOk();

    return [
        'access_token' => $response->json('access_token'),
        'refresh_token' => $response->json('refresh_token'),
    ];
}

test('login issues an access token and a refresh token', function () {
    User::factory()->create(['phone' => '777123456']);

    $tokens = loginAs();

    expect($tokens['access_token'])->toBeString()->not->toBeEmpty();
    expect($tokens['refresh_token'])->toBeString()->not->toBeEmpty();
});

test('a refresh token can be rotated once and the old one is rejected', function () {
    User::factory()->create(['phone' => '777123456']);
    $tokens = loginAs();

    $refreshed = $this->postJson('/api/auth/refresh', [
        'refresh_token' => $tokens['refresh_token'],
    ])->assertOk()->assertJsonStructure(['user', 'access_token', 'refresh_token']);

    expect($refreshed->json('refresh_token'))->not->toBe($tokens['refresh_token']);

    $this->postJson('/api/auth/refresh', [
        'refresh_token' => $tokens['refresh_token'],
    ])->assertUnprocessable();
});

test('refresh rejects an unknown token', function () {
    $this->postJson('/api/auth/refresh', [
        'refresh_token' => 'not-a-real-token',
    ])->assertUnprocessable()->assertJsonValidationErrors('refresh_token');
});

test('an expired refresh token is rejected', function () {
    User::factory()->create(['phone' => '777123456']);
    $tokens = loginAs();

    DB::table('refresh_tokens')
        ->where('token', hash('sha256', $tokens['refresh_token']))
        ->update(['expires_at' => now()->subDay()]);

    $this->postJson('/api/auth/refresh', [
        'refresh_token' => $tokens['refresh_token'],
    ])->assertUnprocessable();
});

test('logout revokes the session so both tokens stop working', function () {
    User::factory()->create(['phone' => '777123456']);
    $tokens = loginAs();

    $this->withHeaders(['Authorization' => 'Bearer '.$tokens['access_token']])
        ->postJson('/api/logout')->assertOk();

    $this->postJson('/api/auth/refresh', [
        'refresh_token' => $tokens['refresh_token'],
    ])->assertUnprocessable();

    // The auth guard caches the user within one test, so forget it to
    // force a fresh token lookup on the next request.
    $this->app['auth']->forgetGuards();

    $this->withHeaders(['Authorization' => 'Bearer '.$tokens['access_token']])
        ->getJson('/api/me')->assertUnauthorized();
});

test('logout everywhere revokes all sessions', function () {
    User::factory()->create(['phone' => '777123456']);
    $first = loginAs();
    $second = loginAs();

    $this->withHeaders(['Authorization' => 'Bearer '.$first['access_token']])
        ->postJson('/api/logout-all')->assertOk();

    $this->postJson('/api/auth/refresh', [
        'refresh_token' => $second['refresh_token'],
    ])->assertUnprocessable();

    $this->app['auth']->forgetGuards();

    $this->withHeaders(['Authorization' => 'Bearer '.$second['access_token']])
        ->getJson('/api/me')->assertUnauthorized();
});

test('a suspended account cannot use protected routes', function () {
    Sanctum::actingAs(User::factory()->suspended()->create());

    $this->getJson('/api/me')->assertForbidden();
});

test('access tokens expire after 60 minutes', function () {
    expect(config('sanctum.expiration'))->toBe(60);
});
