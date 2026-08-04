<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class WebUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\WebUser::create([
            'name' => 'Super Admin',
            'email' => 'admin@singaglobaltekstil.com',
            'password' => \Illuminate\Support\Facades\Hash::make('admin123'),
            'role' => 'super_admin',
            'permissions' => null, // Super admin has all permissions
            'is_active' => true,
        ]);

        \App\Models\WebUser::create([
            'name' => 'User Test',
            'email' => 'user@singaglobaltekstil.com',
            'password' => \Illuminate\Support\Facades\Hash::make('user123'),
            'role' => 'user',
            'permissions' => [
                'view_dashboard',
                'manage_transaksi_km',
                'view_laporan_mutasi',
                'view_laporan_proyeksi',
            ],
            'is_active' => true,
        ]);
    }
}
