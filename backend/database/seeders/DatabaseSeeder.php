<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\UserPreference;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::firstOrCreate(
            ['email' => 'admin@thaidevdocs.local'],
            [
                'name' => 'Admin',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'email_verified_at' => now(),
            ]
        );

        $demoUser = User::firstOrCreate(
            ['email' => 'user@thaidevdocs.local'],
            [
                'name' => 'Demo User',
                'password' => Hash::make('password'),
                'role' => 'user',
                'email_verified_at' => now(),
            ]
        );

        UserPreference::firstOrCreate(['user_id' => $admin->id]);
        UserPreference::firstOrCreate(['user_id' => $demoUser->id]);

        $this->call([
            CategoryTopicSeeder::class,
            ContentSeeder::class,
            GlossarySeeder::class,
            BrowserSeeder::class,
            LearningPathSeeder::class,
        ]);

        $this->command?->info('Database seeded successfully!');
        $this->command?->info('Admin login: admin@thaidevdocs.local / password');
        $this->command?->info('User login: user@thaidevdocs.local / password');
    }
}
