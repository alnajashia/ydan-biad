<?php

namespace Database\Seeders;

use App\Features\Customers\Infrastructure\Persistence\Models\Customer;
use App\Features\Users\Infrastructure\Persistence\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $user = User::factory()->create([
            'full_name' => 'Test User',
            'phone' => '777000000',
        ]);

        Customer::factory()->create([
            'user_id' => $user->id,
        ]);
    }
}
