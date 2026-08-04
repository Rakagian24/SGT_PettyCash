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
        $prefix = "KM-{$code}-{$yymm}-";

        // Find last record for this period and type
        $last = static::where('no_km', 'like', "{$prefix}%")
            ->orderBy('idx', 'desc')
            ->first();

        $seq = 1;
        if ($last) {
            $lastSeq = (int) substr($last->no_km, strlen($prefix));
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
        $prefix = "KM-{$code}-{$yymm}-";

        // Find last record for this period and type (excluding current record)
        $last = static::where('no_km', 'like', "{$prefix}%")
            ->where('idx', '!=', $excludeIdx)
            ->orderBy('idx', 'desc')
            ->first();

        $seq = 1;
        if ($last) {
            $lastSeq = (int) substr($last->no_km, strlen($prefix));
            $seq = $lastSeq + 1;
        }

        return $prefix . str_pad($seq, 4, '0', STR_PAD_LEFT);
    }

    protected static function booted()
    {
        static::creating(function ($model) {
            if (empty($model->no_km) && $model->id_jenis_kas) {
                $map = [
                    1 => 'KGS',
                    2 => 'OGS',
                    3 => 'PGS',
                    4 => 'SGT',
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

        static::updating(function ($model) {
            // Check if date changed to different month/year
            if ($model->isDirty('tanggal_km') || $model->isDirty('id_jenis_kas')) {
                $originalDate = $model->getOriginal('tanggal_km');
                $newDate = $model->tanggal_km;
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
                    $prefix = "KM-{$code}-{$yymm}-";

                    // Find last record for this period and type (excluding current record)
                    $last = static::where('no_km', 'like', "{$prefix}%")
                        ->where('idx', '!=', $model->idx) // Exclude current record
                        ->orderBy('idx', 'desc')
                        ->first();

                    $seq = 1;
                    if ($last) {
                        $lastSeq = (int) substr($last->no_km, strlen($prefix));
                        $seq = $lastSeq + 1;
                    }

                    $model->no_km = $prefix . str_pad($seq, 4, '0', STR_PAD_LEFT);
                }
            }
        });
    }
}
