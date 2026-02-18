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
        Schema::table('transaksi_kk', function (Blueprint $table) {
            // Tambahkan timestamps dengan nullable untuk data existing
            $table->timestamp('created_at')->nullable()->after('status');
            $table->timestamp('updated_at')->nullable()->after('created_at');
        });
        
        // Set created_at dan updated_at untuk data existing
        // Gunakan tanggal_kk sebagai created_at untuk data lama
        DB::statement('UPDATE transaksi_kk SET created_at = tanggal_kk, updated_at = tanggal_kk WHERE created_at IS NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transaksi_kk', function (Blueprint $table) {
            $table->dropColumn(['created_at', 'updated_at']);
        });
    }
};
