<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Belépési adatok: user@example.com / 12345678
        User::factory()->create([
            'name' => 'user',
            'email' => 'user@example.com',
            'password' => '12345678',
        ]);

        $this->call(CountyCitySeeder::class);
    }
}
