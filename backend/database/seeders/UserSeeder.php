<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // System Administrator
        User::firstOrCreate(
            ['email' => 'admin@medicon.health'],
            [
                'name' => 'Dr. Eleanor Vance (Chief Medical Officer)',
                'password' => Hash::make('Secret123!'),
                'role' => UserRole::ADMIN,
                'phone' => '+1 (555) 010-0099',
                'avatar_url' => 'https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?w=150&auto=format&fit=crop&q=80',
                'email_verified_at' => now(),
                'is_active' => true,
            ]
        );
    }
}
