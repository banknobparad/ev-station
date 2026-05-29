<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoUserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['phone' => '0812345678', 'role' => 'driver'],
            [
                'name'     => 'Driver Demo',
                'email'    => null,
                'password' => Hash::make('0812345678'),
                'status'   => 'active',
            ]
        );

        User::updateOrCreate(
            ['email' => 'provider@ev.com', 'role' => 'provider'],
            [
                'name'     => 'Provider Demo',
                'phone'    => '0822222222',
                'password' => Hash::make('password'),
                'status'   => 'active',
            ]
        );
    }
}
