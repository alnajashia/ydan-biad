<?php

namespace Database\Factories;

use App\Features\Lookups\Infrastructure\Persistence\Models\Status;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Status>
 */
class StatusFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<Status>
     */
    protected $model = Status::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->word(),
            'value' => true,
        ];
    }
}
