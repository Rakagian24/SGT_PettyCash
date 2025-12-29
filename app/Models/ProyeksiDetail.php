<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProyeksiDetail extends Model
{
    protected $table = 'proyeksi_dtl';
    protected $primaryKey = 'id_proyeksi_dtl';

    protected $fillable = [
        'id_proyeksi',
        'id_klasifikasi',
        'nominal_proyeksi',
    ];

    protected $casts = [
        'nominal_proyeksi' => 'decimal:2',
    ];

    public $timestamps = false;

    /**
     * Relationship dengan Proyeksi
     */
    public function proyeksi(): BelongsTo
    {
        return $this->belongsTo(Proyeksi::class, 'id_proyeksi', 'id_proyeksi');
    }

    /**
     * Relationship dengan MasterKlasifikasi
     */
    public function klasifikasi(): BelongsTo
    {
        return $this->belongsTo(MasterKlasifikasi::class, 'id_klasifikasi', 'id_klasifikasi');
    }
}