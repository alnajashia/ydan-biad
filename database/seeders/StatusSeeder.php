<?php

namespace Database\Seeders;

use App\Features\Lookups\Infrastructure\Persistence\Models\Status;
use Illuminate\Database\Seeder;

class StatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $statuses = [
            'active' => true,
            'inactive' => false,
            'suspend' => false,
            'prohibited' => false,
        ];

        foreach ($statuses as $name => $value) {
            Status::query()->updateOrCreate(
                ['name' => $name],
                ['value' => $value],
            );
        }
    }
}
