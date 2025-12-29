<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Update existing roles to new values
        DB::table('web_users')->where('role', 'admin')->update(['role' => 'user']);
        DB::table('web_users')->where('role', 'kasir')->update(['role' => 'user']);
        DB::table('web_users')->where('role', 'viewer')->update(['role' => 'user']);
        
        // Modify the enum column
        DB::statement("ALTER TABLE web_users MODIFY COLUMN role ENUM('super_admin', 'user') DEFAULT 'user'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert back to original enum
        DB::statement("ALTER TABLE web_users MODIFY COLUMN role ENUM('super_admin', 'admin', 'kasir', 'viewer') DEFAULT 'viewer'");
    }
};