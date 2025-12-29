<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\WebUser;

class AddPengajuanBudgetPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Update existing users to include the new permission
        $users = WebUser::where('role', 'user')->get();
        
        foreach ($users as $user) {
            $permissions = $user->permissions ?? [];
            
            // Add the new permission if not already present
            if (!in_array('view_pengajuan_budget', $permissions)) {
                $permissions[] = 'view_pengajuan_budget';
                $user->permissions = $permissions;
                $user->save();
            }
        }
        
        $this->command->info('Added Pengajuan Budget permission to ' . $users->count() . ' users.');
    }
}