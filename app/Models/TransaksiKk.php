<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransaksiKk extends Model
{
    protected $table = 'transaksi_kk';
    protected $primaryKey = 'idx';
    public $timestamps = true; // Enable timestamps
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

    /**
     * Get next transaction number for preview
     */
    public static function getNextTransactionNumber($jenisKas, $tanggal = null)
    {
        $map = [
            1 => 'KGS',
            2 => 'OGS',
            3 => 'PGS',
            4 => 'SGT',
        ];

        $code = $map[$jenisKas] ?? 'GEN';
        $date = $tanggal ? \Carbon\Carbon::parse($tanggal) : now();
        $yymm = $date->format('ym');
        $prefix = "KK-{$code}-{$yymm}-";

        // Find last record for this period and type
        $last = static::where('no_kk', 'like', "{$prefix}%")
            ->orderBy('idx', 'desc')
            ->first();

        $seq = 1;
        if ($last) {
            $lastSeq = (int) substr($last->no_kk, strlen($prefix));
            $seq = $lastSeq + 1;
        }

        return $prefix . str_pad($seq, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Get preview transaction number for edit (excluding current record)
     */
    public static function getPreviewNumberForEdit($jenisKas, $tanggal, $excludeIdx)
    {
        $map = [
            1 => 'KGS',
            2 => 'OGS',
            3 => 'PGS',
            4 => 'SGT',
        ];

        $code = $map[$jenisKas] ?? 'GEN';
        $date = \Carbon\Carbon::parse($tanggal);
        $yymm = $date->format('ym');
        $prefix = "KK-{$code}-{$yymm}-";

        // Find last record for this period and type (excluding current record)
        $last = static::where('no_kk', 'like', "{$prefix}%")
            ->where('idx', '!=', $excludeIdx)
            ->orderBy('idx', 'desc')
            ->first();

        $seq = 1;
        if ($last) {
            $lastSeq = (int) substr($last->no_kk, strlen($prefix));
            $seq = $lastSeq + 1;
        }

        return $prefix . str_pad($seq, 4, '0', STR_PAD_LEFT);
    }

    protected static function booted()
    {
        static::creating(function ($model) {
            if (empty($model->no_kk) && $model->id_jenis_kas) {
                $map = [
                    1 => 'KGS',
                    2 => 'OGS',
                    3 => 'PGS',
                    4 => 'SGT',
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

        static::updating(function ($model) {
            // Check if date changed to different month/year
            if ($model->isDirty('tanggal_kk') || $model->isDirty('id_jenis_kas')) {
                $originalDate = $model->getOriginal('tanggal_kk');
                $newDate = $model->tanggal_kk;
                $originalJenisKas = $model->getOriginal('id_jenis_kas');
                $newJenisKas = $model->id_jenis_kas;

                // Parse dates
                $originalPeriod = $originalDate ? \Carbon\Carbon::parse($originalDate)->format('ym') : null;
                $newPeriod = $newDate ? \Carbon\Carbon::parse($newDate)->format('ym') : null;

                // Update transaction number if period or jenis kas changed
                if ($originalPeriod !== $newPeriod || $originalJenisKas !== $newJenisKas) {
                    $map = [
                        1 => 'KGS',
                        2 => 'OGS',
                        3 => 'PGS',
                        4 => 'SGT',
                    ];
                    $code = $map[$newJenisKas] ?? 'GEN';
                    $date = \Carbon\Carbon::parse($newDate);
                    $yymm = $date->format('ym');
                    $prefix = "KK-{$code}-{$yymm}-";

                    // Find last record for this period and type (excluding current record)
                    $last = static::where('no_kk', 'like', "{$prefix}%")
                        ->where('idx', '!=', $model->idx) // Exclude current record
                        ->orderBy('idx', 'desc')
                        ->first();

                    $seq = 1;
                    if ($last) {
                        $lastSeq = (int) substr($last->no_kk, strlen($prefix));
                        $seq = $lastSeq + 1;
                    }

                    $model->no_kk = $prefix . str_pad($seq, 4, '0', STR_PAD_LEFT);
                }
            }
        });
    }
}
