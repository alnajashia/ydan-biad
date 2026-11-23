<?php

namespace Database\Factories;

use App\Features\Lookups\Infrastructure\Persistence\Models\Role;
use App\Features\Lookups\Infrastructure\Persistence\Models\Status;
use App\Features\Users\Domain\Enums\Gender;
use App\Features\Users\Domain\Enums\IdentityType;
use App\Features\Users\Infrastructure\Persistence\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<User>
     */
    protected $model = User::class;

    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'full_name' => fake()->name(),
            'phone' => fake()->unique()->numerify('7########'),
            'password' => static::$password ??= Hash::make('password'),
            'gender' => fake()->randomElement(Gender::cases()),
            'status_id' => fn () => Status::query()->firstOrCreate(['name' => 'active'], ['value' => true])->id,
            'identity_type' => fake()->randomElement(IdentityType::cases()),
            'identity_number' => fake()->unique()->numerify('##########'),
            'identity_expiry_date' => fake()->dateTimeBetween('+1 year', '+5 years')->format('Y-m-d'),
            'account_verified_at' => now(),
            'role_id' => fn () => Role::query()->firstOrCreate(['name' => 'customer'], ['value' => true])->id,
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Indicate that the user's account should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'account_verified_at' => null,
        ]);
    }
}
