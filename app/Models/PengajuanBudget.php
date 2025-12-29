<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PengajuanBudget extends Model
{
    protected $table = 'pengajuan_budget';
    protected $primaryKey = 'id_pengajuan_budget';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'id_pengajuan_budget',
        'tgl_input',
        'tgl_dari',
        'tgl_sampai',
        'id_jenis_kas',
        'kisaran_saldo',
        'nominal_pengajuan',
    ];

    protected $casts = [
        'tgl_input' => 'datetime',
        'tgl_dari' => 'date',
        'tgl_sampai' => 'date',
        'kisaran_saldo' => 'decimal:2',
        'nominal_pengajuan' => 'decimal:2',
    ];

    /**
     * Relationship dengan PengajuanBudgetDetail
     */
    public function details(): HasMany
    {
        return $this->hasMany(PengajuanBudgetDetail::class, 'id_pengajuan_budget', 'id_pengajuan_budget');
    }

    /**
     * Relationship dengan MasterJenisKas
     */
    public function jenisKas(): BelongsTo
    {
        return $this->belongsTo(MasterJenisKas::class, 'id_jenis_kas', 'id_jenis_kas');
    }

    /**
     * Generate nomor PGB otomatis
     */
    public static function generatePGBNumber(): string
    {
        $latest = self::where('id_pengajuan_budget', 'like', 'PGB%')
            ->orderByRaw('CAST(SUBSTRING(id_pengajuan_budget, 4) AS UNSIGNED) DESC')
            ->first();

        $latestNumber = 0;
        if ($latest) {
            $latestNumber = (int) substr($latest->id_pengajuan_budget, 3);
        }

        $newNumber = $latestNumber + 1;
        return 'PGB' . str_pad($newNumber, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Get total pengajuan
     */
    public function getTotalPengajuanAttribute(): float
    {
        return $this->details->sum('nominal_pengajuan_dtl');
    }
}