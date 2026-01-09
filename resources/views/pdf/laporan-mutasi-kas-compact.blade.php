<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Laporan Mutasi Kas {{ $jenisKas }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
            line-height: 1.3;
            color: #000;
            padding: 15px;
        }
        
        .header {
            text-align: left;
            margin-bottom: 25px;
        }
        
        .header h1 {
            font-size: 13px;
            font-weight: bold;
            margin-bottom: 8px;
        }
        
        .header h2 {
            font-size: 11px;
            font-weight: bold;
            border: 1px solid #000;
            padding: 5px 8px;
            display: inline-block;
            margin-bottom: 15px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9px;
            table-layout: fixed;
        }
        
        th, td {
            border: 1px solid #000;
            padding: 4px 5px;
            text-align: left;
            vertical-align: top;
            word-wrap: break-word;
            overflow: hidden;
        }
        
        th {
            background-color: #f5f5f5;
            font-weight: bold;
            text-align: center;
            font-size: 9px;
        }
        
        .text-center {
            text-align: center;
        }
        
        .text-right {
            text-align: right;
        }
        
        .currency {
            text-align: right;
            font-family: 'Courier New', monospace;
        }
        
        .total-row {
            font-weight: bold;
            background-color: #f9f9f9;
            border-top: 2px solid #000;
        }
        
        .keterangan {
            max-width: 180px;
            word-wrap: break-word;
            font-size: 8px;
            line-height: 1.3;
            overflow: hidden;
        }
        
        .no-wrap {
            white-space: nowrap;
        }
        
        /* Kolom width untuk portrait - disesuaikan agar pas A4 */
        .col-tanggal { width: 70px; }
        .col-nomor { width: 85px; }
        .col-dari { width: 80px; }
        .col-keterangan { width: 180px; }
        .col-km { width: 75px; }
        .col-kk { width: 75px; }
        .col-saldo { width: 85px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>LAPORAN MUTASI {{ $jenisKas }} PT. GUNAJAYA SANTOSA</h1>
        <h2>{{ $periodeText }}</h2>
    </div>
    
    <table>
        <thead>
            <tr>
                <th class="col-tanggal">TANGGAL</th>
                <th class="col-nomor">NOMOR<br>KM/KK</th>
                <th class="col-dari">DARI/KE</th>
                <th class="col-keterangan">KETERANGAN</th>
                <th class="col-km">KM</th>
                <th class="col-kk">KK</th>
                <th class="col-saldo">SALDO</th>
            </tr>
        </thead>
        <tbody>
            @php
                $totalKM = 0;
                $totalKK = 0;
                $saldoAkhir = 0;
            @endphp
            
            @foreach($data as $row)
                @php
                    $totalKM += $row['nominal_pemasukan'];
                    $totalKK += $row['nominal_pengeluaran'];
                    $saldoAkhir = $row['saldo_akhir'];
                    
                    // Format tanggal
                    $tanggal = $row['tanggal'];
                    if ($tanggal !== '-' && !empty($tanggal)) {
                        try {
                            $tanggal = \Carbon\Carbon::parse($tanggal)->format('d-M-y');
                        } catch (\Exception $e) {
                            // Keep original if parsing fails
                        }
                    }
                    
                    // Clean whitespace untuk keterangan
                    $keterangan = trim($row['keterangan']);
                    $keterangan = preg_replace('/\s+/', ' ', $keterangan);
                    $keterangan = preg_replace('/[\r\n\t]/', ' ', $keterangan);
                    $keterangan = trim($keterangan);
                @endphp
                
                <tr>
                    <td class="text-center no-wrap">{{ $tanggal }}</td>
                    <td class="text-center no-wrap">{{ $row['no_transaksi'] }}</td>
                    <td>{{ trim($row['jenis_transaksi']) }}</td>
                    <td class="keterangan">{{ $keterangan }}</td>
                    <td class="currency">
                        @if($row['nominal_pemasukan'] > 0)
                            {{ number_format($row['nominal_pemasukan'], 2, '.', ',') }}
                        @else
                            -
                        @endif
                    </td>
                    <td class="currency">
                        @if($row['nominal_pengeluaran'] > 0)
                            {{ number_format($row['nominal_pengeluaran'], 2, '.', ',') }}
                        @else
                            -
                        @endif
                    </td>
                    <td class="currency">{{ number_format($row['saldo_akhir'], 2, '.', ',') }}</td>
                </tr>
            @endforeach
            
            <!-- Baris Total -->
            <tr class="total-row">
                <td colspan="4" class="text-center"><strong>TOTAL</strong></td>
                <td class="currency"><strong>{{ number_format($totalKM, 2, '.', ',') }}</strong></td>
                <td class="currency"><strong>{{ number_format($totalKK, 2, '.', ',') }}</strong></td>
                <td class="currency"><strong>{{ number_format($saldoAkhir, 2, '.', ',') }}</strong></td>
            </tr>
        </tbody>
    </table>
</body>
</html>