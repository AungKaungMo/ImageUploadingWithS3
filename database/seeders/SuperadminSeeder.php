<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class SuperadminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::create([
            'name' => 'Admin',
            'email' => 'admin@gmail.com',
            'phone' => '09123456789',
            'password' => bcrypt('adminpass'),
            'type' => 'ADMIN'
        ]);

        $user->assignRole('admin');
    }
}
