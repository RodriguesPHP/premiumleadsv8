<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Ramsey\Uuid\Uuid;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $db = config('database.connections.mysql.database');
        // User::factory(10)->create();
        User::factory()->create([
            'uuid'=> Uuid::uuid4()->toString(),
            'name' => "Admin {$db}",
            'email' => "admin@{$db}.com",
            'password'=> bcrypt('102030@@'),
            'role'=>"admin"
        ]);
    }
}
