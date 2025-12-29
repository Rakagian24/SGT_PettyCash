<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PengajuanBudgetDetail extends Model
{
    protected $table = 'pengajuan_budget_dtl';
    protected $primaryKey = 'id_pengajuan_budget_dtl';
    public $timestamps = false;

    protected $fillable = [
        'id_pengajuan_budget',
        'keterangan',
        'id_klasifikasi',
        'lampiran',
        'nominal_pengajuan_dtl',
    ];

    protected $casts = [
        'nominal_pengajuan_dtl' => 'decimal:2',
    ];

    /**
     * Relationship dengan PengajuanBudget
     */
    public function pengajuanBudget(): BelongsTo
    {
        return $this->belongsTo(PengajuanBudget::class, 'id_pengajuan_budget', 'id_pengajuan_budget');
    }

    /**
     * Relationship dengan MasterKlasifikasi
     */
    public function klasifikasi(): BelongsTo
    {
        return $this->belongsTo(MasterKlasifikasi::class, 'id_klasifikasi', 'id_klasifikasi');
    }
}