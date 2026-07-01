<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
{
    User::create([
        'name' => 'Owner MR. CHICKEN',
        'username' => 'owner',
        'password' => Hash::make('owner123'),
        'role' => 'owner',
        'is_active' => true,
    ]);

    User::create([
        'name' => 'Karyawan ',
        'username' => 'karyawan',
        'password' => Hash::make('karyawan123'),
        'role' => 'karyawan',
        'is_active' => true,
    ]);
}
}
