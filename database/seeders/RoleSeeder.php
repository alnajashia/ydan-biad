<?php

namespace Database\Seeders;

use App\Features\Lookups\Infrastructure\Persistence\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            'admin' => true,
            'employee' => true,
            'customer' => true,
        ];

        foreach ($roles as $name => $value) {
            Role::query()->updateOrCreate(
                ['name' => $name],
                ['value' => $value],
            );
        }
    }
}
