<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Pengajuan Budget {{ $pengajuanBudget->jenisKas->jenis_kas }}</title>
    <style>
        table { border-collapse: collapse; width: 100%; }
        
        /* No borders for header rows */
        .no-border { border: none; padding: 8px; }
        
        /* Borders only for data table */
        .data-table th, .data-table td { 
            border: 1px solid #000; 
            padding: 8px; 
            text-align: left; 
        }
        
        .data-table th { 
            background-color: #f2f2f2; 
            font-weight: bold; 
            text-align: center;
        }
        
        .header { font-weight: bold; font-size: 14px; }
        .right { text-align: right; }
        .center { text-align: center; }
        
        /* Column widths */
        .col-keterangan { width: 200px; }
        .col-id { width: 120px; }
        .col-kriteria { width: 200px; }
        .col-klasifikasi { width: 200px; }
        .col-lampiran { width: 100px; }
        .col-nominal { width: 150px; }
    </style>
</head>
<body>
    <table>
        <!-- Header rows without borders -->
        <tr>
            <td colspan="6" class="no-border header">PENGAJUAN BUDGET {{ strtoupper($pengajuanBudget->jenisKas->jenis_kas) }}</td>
        </tr>
        <tr>
            <td colspan="6" class="no-border header">PERIODE {{ $pengajuanBudget->tgl_dari->format('d-M-Y') }} S/D {{ $pengajuanBudget->tgl_sampai->format('d-M-Y') }}</td>
        </tr>
        <tr><td colspan="6" class="no-border"></td></tr>
    </table>
    
    <!-- Data table with borders -->
    <table class="data-table">
        <tr>
            <th class="col-keterangan">KETERANGAN</th>
            <th class="col-id">ID KLASIFIKASI</th>
            <th class="col-kriteria">KRITERIA</th>
            <th class="col-klasifikasi">KLASIFIKASI</th>
            <th class="col-lampiran">LAMPIRAN</th>
            <th class="col-nominal">NOMINAL</th>
        </tr>
        @foreach($pengajuanBudget->details as $detail)
        <tr>
            <td class="col-keterangan">{{ $detail->keterangan }}</td>
            <td class="col-id">{{ $detail->id_klasifikasi }}</td>
            <td class="col-kriteria">{{ $detail->klasifikasi->kriteria ?? '' }}</td>
            <td class="col-klasifikasi">{{ $detail->klasifikasi->klasifikasi ?? '' }}</td>
            <td class="col-lampiran center">{{ $detail->lampiran }}</td>
            <td class="col-nominal right">{{ number_format($detail->nominal_pengajuan_dtl, 2, '.', ',') }}</td>
        </tr>
        @endforeach
        <tr>
            <td colspan="5" class="header center">TOTAL</td>
            <td class="right header">{{ number_format($pengajuanBudget->details->sum('nominal_pengajuan_dtl'), 2, '.', ',') }}</td>
        </tr>
    </table>
    
    <br>
    
    <!-- Summary information -->
    <table>
        <tr>
            <td colspan="4" class="no-border header">KISARAN SISA SALDO</td>
            <td colspan="2" class="no-border right header">{{ number_format($pengajuanBudget->kisaran_saldo, 2, '.', ',') }}</td>
        </tr>
        <tr>
            <td colspan="4" class="no-border header">PENGAJUAN</td>
            <td colspan="2" class="no-border right header">{{ number_format($pengajuanBudget->nominal_pengajuan, 2, '.', ',') }}</td>
        </tr>
    </table>
</body>
</html>