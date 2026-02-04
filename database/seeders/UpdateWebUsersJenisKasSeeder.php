<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\WebUser;

class UpdateWebUsersJenisKasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Update existing users to have access to all jenis kas by default
        WebUser::whereNull('allowed_jenis_kas')
            ->orWhere('allowed_jenis_kas', '[]')
            ->update(['allowed_jenis_kas' => json_encode([1, 2, 3, 4])]);
        
        $this->command->info('Updated existing web users with default allowed_jenis_kas');
    }
}
