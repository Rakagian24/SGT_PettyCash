<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Proyeksi extends Model
{
    protected $table = 'proyeksi';
    protected $primaryKey = 'id_proyeksi';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false; // Disable timestamps since table doesn't have created_at/updated_at

    protected $fillable = [
        'id_proyeksi',
        'tgl_input',
        'tgl_dari',
        'tgl_sampai',
        'id_jenis_kas',
        'kisaran_sawal',
    ];

    protected $casts = [
        'tgl_input' => 'datetime',
        'tgl_dari' => 'date',
        'tgl_sampai' => 'date',
        'kisaran_sawal' => 'decimal:2',
    ];

    /**
     * Relationship dengan ProyeksiDetail
     */
    public function details(): HasMany
    {
        return $this->hasMany(ProyeksiDetail::class, 'id_proyeksi', 'id_proyeksi');
    }

    /**
     * Relationship dengan MasterJenisKas
     */
    public function jenisKas(): BelongsTo
    {
        return $this->belongsTo(MasterJenisKas::class, 'id_jenis_kas', 'id_jenis_kas');
    }

    /**
     * Generate nomor PRK otomatis
     */
    public static function generatePRKNumber(): string
    {
        $latest = self::where('id_proyeksi', 'like', 'PRK%')
            ->orderByRaw('CAST(SUBSTRING(id_proyeksi, 4) AS UNSIGNED) DESC')
            ->first();

        $latestNumber = 0;
        if ($latest) {
            $latestNumber = (int) substr($latest->id_proyeksi, 3);
        }

        $newNumber = $latestNumber + 1;
        return 'PRK' . str_pad($newNumber, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Get total proyeksi
     */
    public function getTotalProyeksiAttribute(): float
    {
        return $this->details->sum('nominal_proyeksi');
    }
}