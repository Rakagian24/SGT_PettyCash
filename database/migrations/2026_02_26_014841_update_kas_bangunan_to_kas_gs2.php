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
        // Update nama jenis kas dari "KAS BANGUNAN" menjadi "KAS GS2"
        DB::table('master_jenis_kas')
            ->where('jenis_kas', 'KAS BANGUNAN')
            ->update(['jenis_kas' => 'KAS GS2']);
        
        // Update prefix transaksi kas masuk dari BGS menjadi GS2
        DB::table('transaksi_km')
            ->where('no_km', 'LIKE', 'KM-BGS-%')
            ->update([
                'no_km' => DB::raw("REPLACE(no_km, 'KM-BGS-', 'KM-GS2-')")
            ]);
        
        // Update prefix transaksi kas keluar dari BGS menjadi GS2
        DB::table('transaksi_kk')
            ->where('no_kk', 'LIKE', 'KK-BGS-%')
            ->update([
                'no_kk' => DB::raw("REPLACE(no_kk, 'KK-BGS-', 'KK-GS2-')")
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Rollback: kembalikan ke KAS BANGUNAN
        DB::table('master_jenis_kas')
            ->where('jenis_kas', 'KAS GS2')
            ->update(['jenis_kas' => 'KAS BANGUNAN']);
        
        // Rollback: kembalikan prefix transaksi kas masuk
        DB::table('transaksi_km')
            ->where('no_km', 'LIKE', 'KM-GS2-%')
            ->update([
                'no_km' => DB::raw("REPLACE(no_km, 'KM-GS2-', 'KM-BGS-')")
            ]);
        
        // Rollback: kembalikan prefix transaksi kas keluar
        DB::table('transaksi_kk')
            ->where('no_kk', 'LIKE', 'KK-GS2-%')
            ->update([
                'no_kk' => DB::raw("REPLACE(no_kk, 'KK-GS2-', 'KK-BGS-')")
            ]);
    }
};
