<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\WebUser;

class UpdateWebUserPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Update existing users with new permission structure
        $users = WebUser::where('role', 'user')->get();
        
        foreach ($users as $user) {
            // Give all permissions to existing users (they can be adjusted later)
            $user->permissions = [
                'view_dashboard',
                'view_transaksi_km',
                'view_transaksi_kk',
                'view_laporan_mutasi',
                'view_proyeksi',
                'view_master_bayar',
                'view_master_terima',
                'view_master_klasifikasi',
                'view_master_jenis_kas',
            ];
            $user->save();
        }
        
        $this->command->info('Updated permissions for ' . $users->count() . ' users.');
    }
}