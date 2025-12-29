<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransaksiKm extends Model
{
    protected $table = 'transaksi_km';
    protected $primaryKey = 'idx';
    public $timestamps = false;
    protected $fillable = [
        'tanggal_km',
        'id_jenis_kas',
        'no_km',
        'id_terima',
        'nominal_km',
        'id_klasifikasi',
        'keterangan_km',
        'pembuat',
        'status'
    ];

    /**
     * Relationship to MasterJenisKas
     */
    public function jenisKas()
    {
        return $this->belongsTo(MasterJenisKas::class, 'id_jenis_kas', 'id_jenis_kas');
    }

    /**
     * Relationship to MasterTerima
     */
    public function masterTerima()
    {
        return $this->belongsTo(MasterTerima::class, 'id_terima', 'id_terima');
    }

    /**
     * Relationship to MasterTerima (alias untuk backward compatibility)
     */
    public function terima()
    {
        return $this->belongsTo(MasterTerima::class, 'id_terima', 'id_terima');
    }

    /**
     * Relationship to MasterKlasifikasi
     */
    public function klasifikasi()
    {
        return $this->belongsTo(MasterKlasifikasi::class, 'id_klasifikasi', 'id_klasifikasi');
    }

    protected static function booted()
    {
        static::creating(function ($model) {
            if (empty($model->no_km) && $model->id_jenis_kas) {
                $map = [
                    1 => 'KGS',
                    2 => 'OGS',
                    3 => 'PGS',
                    4 => 'BGS',
                ];
                $code = $map[$model->id_jenis_kas] ?? 'GEN';
                // Assuming tanggal_km is cast to date or string. If string, parse it.
                $date = $model->tanggal_km ? \Carbon\Carbon::parse($model->tanggal_km) : now();
                $yymm = $date->format('ym'); 
                $prefix = "KM-{$code}-{$yymm}-";

                // Find last record for this period and type
                $last = static::where('no_km', 'like', "{$prefix}%")
                    ->orderBy('idx', 'desc') // idx is PK, safer for ordering insertion
                    ->first();
                
                $seq = 1;
                if ($last) {
                    // Extract sequence. 
                    // Format: KM-KGS-2412-00001
                    // Length of prefix is variable? No, fixed format IF code is 3 chars. 
                    // KM is 2, - is 1, KGS is 3, - is 1, 2412 is 4, - is 1. Total 12 chars prefix.
                    // But safest is substr.
                    $lastSeq = (int) substr($last->no_km, strlen($prefix));
                    $seq = $lastSeq + 1;
                }
                
                
                $model->no_km = $prefix . str_pad($seq, 4, '0', STR_PAD_LEFT);
            }
        });
    }
}
