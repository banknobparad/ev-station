<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@ev.com', 'role' => 'admin'],
            [
                'name'     => 'Admin',
                'phone'    => '0999999999',
                'password' => Hash::make('password'),
                'status'   => 'active',
            ]
        );
    }
}