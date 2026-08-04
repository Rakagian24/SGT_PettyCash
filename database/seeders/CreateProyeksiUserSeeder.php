<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\WebUser;
use Illuminate\Support\Facades\Hash;

class CreateProyeksiUserSeeder extends Seeder
{
    public function run(): void
    {
        // Buat user baru dengan permission proyeksi
        WebUser::updateOrCreate(
            ['email' => 'proyeksi@singaglobaltekstil.com'],
            [
                'name' => 'User Proyeksi',
                'password' => Hash::make('proyeksi123'),
                'role' => 'user',
                'permissions' => [
                    'view_dashboard',
                    'view_laporan_proyeksi',
                ],
                'is_active' => true,
            ]
        );

        echo "User proyeksi@singaglobaltekstil.com created/updated\n";
    }
}