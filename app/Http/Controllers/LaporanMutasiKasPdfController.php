<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class LaporanMutasiKasPdfController extends Controller
{
    public function generatePdf(Request $request)
    {
        // Increase memory limit untuk data banyak
        ini_set('memory_limit', '512M');
        
        $sessionKey = $request->get('session_key');
        
        if (!$sessionKey) {
            abort(404, 'Session key tidak ditemukan');
        }
        
        $sessionData = session($sessionKey);
        
        if (!$sessionData) {
            abort(404, 'Data tidak ditemukan atau sudah expired');
        }
        
        // Check if data has expired
        if (isset($sessionData['expires_at']) && now()->gt($sessionData['expires_at'])) {
            session()->forget($sessionKey);
            abort(404, 'Data sudah expired');
        }
        
        $jenisKas = $sessionData['jenis_kas'] ?? 'KAS';
        $dari = $sessionData['dari'] ?? '';
        $sampai = $sessionData['sampai'] ?? '';
        $data = $sessionData['data'] ?? [];
        
        if (empty($data)) {
            abort(404, 'Data tidak ditemukan');
        }
        
        // Format periode yang lebih lengkap
        $periodeText = "Periode {$dari} s/d {$sampai}";
        
        try {
            // Generate PDF langsung
            $pdf = Pdf::loadView('pdf.laporan-mutasi-kas-v4', compact('jenisKas', 'periodeText', 'data'));
            
            // Set paper size dan orientation
            $pdf->setPaper('A4', 'portrait');
            
            // Set options untuk kualitas yang lebih baik
            $pdf->setOptions([
                'isHtml5ParserEnabled' => true,
                'isPhpEnabled' => true,
                'defaultFont' => 'Arial'
            ]);
            
            // Generate filename
            $filename = "Laporan_Mutasi_Kas_{$jenisKas}_Periode_" . str_replace([' ', '/'], '_', "{$dari}_sd_{$sampai}") . ".pdf";
            
            // Stream PDF ke browser (langsung preview)
            return $pdf->stream($filename);
            
        } catch (\Exception $e) {
            \Log::error('PDF Generation Error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'session_key' => $sessionKey
            ]);
            
            abort(500, 'Terjadi kesalahan saat generate PDF: ' . $e->getMessage());
        }
    }
}