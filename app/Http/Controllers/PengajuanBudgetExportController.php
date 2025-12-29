<?php

namespace App\Http\Controllers;

use App\Models\PengajuanBudget;
use Illuminate\Http\Request;

class PengajuanBudgetExportController extends Controller
{
    public function export($id)
    {
        $pengajuanBudget = PengajuanBudget::with(['jenisKas', 'details.klasifikasi'])
            ->where('id_pengajuan_budget', $id)
            ->first();
        
        if (!$pengajuanBudget) {
            abort(404, 'Pengajuan Budget tidak ditemukan');
        }
        
        $filename = 'Pengajuan_Budget_' . str_replace([' ', '/', '\\'], '_', $pengajuanBudget->jenisKas->jenis_kas) . '_' . $pengajuanBudget->id_pengajuan_budget . '.xls';
        
        $html = view('exports.pengajuan-budget', compact('pengajuanBudget'))->render();
        
        return response($html)
            ->header('Content-Type', 'application/vnd.ms-excel')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->header('Pragma', 'no-cache')
            ->header('Cache-Control', 'must-revalidate, post-check=0, pre-check=0')
            ->header('Expires', '0');
    }
}