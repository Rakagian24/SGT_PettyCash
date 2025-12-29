<?php

namespace App\Http\Controllers;

use App\Models\Proyeksi;
use Illuminate\Http\Request;

class ProyeksiExportController extends Controller
{
    public function export($id)
    {
        $proyeksi = Proyeksi::with(['jenisKas', 'details.klasifikasi'])
            ->where('id_proyeksi', $id)
            ->first();
        
        if (!$proyeksi) {
            abort(404, 'Proyeksi tidak ditemukan');
        }
        
        $filename = 'Proyeksi_' . str_replace([' ', '/', '\\'], '_', $proyeksi->jenisKas->jenis_kas) . '_' . $proyeksi->id_proyeksi . '.xls';
        
        $html = view('exports.proyeksi-simple', compact('proyeksi'))->render();
        
        return response($html)
            ->header('Content-Type', 'application/vnd.ms-excel')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->header('Pragma', 'no-cache')
            ->header('Cache-Control', 'must-revalidate, post-check=0, pre-check=0')
            ->header('Expires', '0');
    }
}