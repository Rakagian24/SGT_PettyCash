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
        // Increase pembuat column size in transaksi_km table
        Schema::table('transaksi_km', function (Blueprint $table) {
            $table->string('pembuat', 100)->change();
        });

        // Increase pembuat column size in transaksi_kk table
        Schema::table('transaksi_kk', function (Blueprint $table) {
            $table->string('pembuat', 100)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert pembuat column size in transaksi_km table
        Schema::table('transaksi_km', function (Blueprint $table) {
            $table->string('pembuat', 10)->change();
        });

        // Revert pembuat column size in transaksi_kk table
        Schema::table('transaksi_kk', function (Blueprint $table) {
            $table->string('pembuat', 10)->change();
        });
    }
};