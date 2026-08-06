<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SummaryPengajuanBudget extends Model
{
    protected $table = 'summary_pengajuan_budget';
    protected $primaryKey = 'id_spb';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'id_spb',
        'tgl_input',
        'tgl_dari',
        'tgl_sampai',
        'kgs',
        'ogs',
        'pgs',
        'bgs',
        'pembulatan',
    ];

    protected $casts = [
        'tgl_input' => 'datetime',
        'tgl_dari' => 'date',
        'tgl_sampai' => 'date',
        'kgs' => 'decimal:2',
        'ogs' => 'decimal:2',
        'pgs' => 'decimal:2',
        'bgs' => 'decimal:2',
        'pembulatan' => 'decimal:2',
    ];

    /**
     * Generate nomor SPB otomatis
     */
    public static function generateSPBNumber(): string
    {
        $latest = self::where('id_spb', 'like', 'SPB%')
            ->orderByRaw('CAST(SUBSTRING(id_spb, 4) AS UNSIGNED) DESC')
            ->first();

        $latestNumber = 0;
        if ($latest) {
            $latestNumber = (int) substr($latest->id_spb, 3);
        }

        $newNumber = $latestNumber + 1;
        return 'SPB' . str_pad($newNumber, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Get total pengajuan (kgs + ogs + pgs + bgs)
     */
    public function getTotalAttribute(): float
    {
        return $this->kgs + $this->ogs + $this->pgs + $this->bgs;
    }

    /**
     * Get summary data from pengajuan_budget table
     */
    public static function getSummaryData(?string $tglDari, ?string $tglSampai): array
    {
        $data = [
            'kgs' => 0,
            'ogs' => 0,
            'pgs' => 0,
            'bgs' => 0,
        ];

        $results = \Illuminate\Support\Facades\DB::table('pengajuan_budget as pb')
            ->join('master_jenis_kas as mjk', 'pb.id_jenis_kas', '=', 'mjk.id_jenis_kas')
            ->select('mjk.jenis_kas', \Illuminate\Support\Facades\DB::raw('SUM(pb.nominal_pengajuan) as total_nominal'))
            ->whereIn('pb.id_jenis_kas', ['1', '2', '3', '4'])
            ->where('pb.tgl_dari', '>=', $tglDari)
            ->where('pb.tgl_sampai', '<=', $tglSampai)
            ->groupBy('mjk.jenis_kas')
            ->get();

        foreach ($results as $result) {
            switch ($result->jenis_kas) {
                case 'KAS KECIL':
                    $data['kgs'] = $result->total_nominal;
                    break;
                case 'KAS OFFICE':
                    $data['ogs'] = $result->total_nominal;
                    break;
                case 'KAS PERSONALIA':
                    $data['pgs'] = $result->total_nominal;
                    break;
                case 'KAS SGT':
                    $data['bgs'] = $result->total_nominal;
                    break;
            }
        }

        return $data;
    }
}
