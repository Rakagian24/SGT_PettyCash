<?php

namespace App\Http\Controllers;

use App\Models\SummaryPengajuanBudget;
use Illuminate\Http\Request;

class SummaryPengajuanBudgetExportController extends Controller
{
    public function export($id)
    {
        $summaryPengajuanBudget = SummaryPengajuanBudget::where('id_spb', $id)->first();
        
        if (!$summaryPengajuanBudget) {
            abort(404, 'Summary Pengajuan Budget tidak ditemukan');
        }
        
        $filename = 'Summary_Pengajuan_Budget_' . $summaryPengajuanBudget->id_spb . '_Periode_' . 
                   $summaryPengajuanBudget->tgl_dari->format('d-M-y') . '_sd_' . 
                   $summaryPengajuanBudget->tgl_sampai->format('d-M-y') . '.xls';
        
        $html = view('exports.summary-pengajuan-budget', compact('summaryPengajuanBudget'))->render();
        
        return response($html)
            ->header('Content-Type', 'application/vnd.ms-excel')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->header('Pragma', 'no-cache')
            ->header('Cache-Control', 'must-revalidate, post-check=0, pre-check=0')
            ->header('Expires', '0');
    }
}