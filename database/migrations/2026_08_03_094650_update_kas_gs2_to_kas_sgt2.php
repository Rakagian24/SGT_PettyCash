<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Update nama jenis kas dari "KAS GS2" menjadi "KAS SGT"
        DB::table('master_jenis_kas')
            ->where('jenis_kas', 'KAS GS2')
            ->update(['jenis_kas' => 'KAS SGT']);

        // Update prefix transaksi kas masuk dari GS2 menjadi SGT
        DB::table('transaksi_km')
            ->where('no_km', 'LIKE', 'KM-GS2-%')
            ->update([
                'no_km' => DB::raw("REPLACE(no_km, 'KM-GS2-', 'KM-SGT-')")
            ]);

        // Update prefix transaksi kas keluar dari GS2 menjadi SGT
        DB::table('transaksi_kk')
            ->where('no_kk', 'LIKE', 'KK-GS2-%')
            ->update([
                'no_kk' => DB::raw("REPLACE(no_kk, 'KK-GS2-', 'KK-SGT-')")
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert nama jenis kas dari "KAS SGT" menjadi "KAS GS2"
        DB::table('master_jenis_kas')
            ->where('jenis_kas', 'KAS SGT')
            ->update(['jenis_kas' => 'KAS GS2']);

        // Revert prefix transaksi kas masuk dari SGT menjadi GS2
        DB::table('transaksi_km')
            ->where('no_km', 'LIKE', 'KM-SGT-%')
            ->update([
                'no_km' => DB::raw("REPLACE(no_km, 'KM-SGT-', 'KM-GS2-')")
            ]);

        // Revert prefix transaksi kas keluar dari SGT menjadi GS2
        DB::table('transaksi_kk')
            ->where('no_kk', 'LIKE', 'KK-SGT-%')
            ->update([
                'no_kk' => DB::raw("REPLACE(no_kk, 'KK-SGT-', 'KK-GS2-')")
            ]);
    }
};
