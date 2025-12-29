<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransaksiKk extends Model
{
    protected $table = 'transaksi_kk';
    protected $primaryKey = 'idx';
    public $timestamps = false;
    protected $fillable = [
        'tanggal_kk',
        'id_jenis_kas',
        'no_kk',
        'id_bayar',
        'nominal_kk',
        'id_klasifikasi',
        'keterangan_kk',
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
     * Relationship to MasterBayar
     */
    public function masterBayar()
    {
        return $this->belongsTo(MasterBayar::class, 'id_bayar', 'id_bayar');
    }

    /**
     * Relationship to MasterBayar (alias untuk backward compatibility)
     */
    public function bayar()
    {
        return $this->belongsTo(MasterBayar::class, 'id_bayar', 'id_bayar');
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
            if (empty($model->no_kk) && $model->id_jenis_kas) {
                $map = [
                    1 => 'KGS',
                    2 => 'OGS',
                    3 => 'PGS',
                    4 => 'BGS',
                ];
                $code = $map[$model->id_jenis_kas] ?? 'GEN';
                $date = $model->tanggal_kk ? \Carbon\Carbon::parse($model->tanggal_kk) : now();
                $yymm = $date->format('ym'); 
                $prefix = "KK-{$code}-{$yymm}-";

                $last = static::where('no_kk', 'like', "{$prefix}%")
                    ->orderBy('idx', 'desc')
                    ->first();
                
                $seq = 1;
                if ($last) {
                    $lastSeq = (int) substr($last->no_kk, strlen($prefix));
                    $seq = $lastSeq + 1;
                }
                
                
                $model->no_kk = $prefix . str_pad($seq, 4, '0', STR_PAD_LEFT);
            }
        });
    }
}
