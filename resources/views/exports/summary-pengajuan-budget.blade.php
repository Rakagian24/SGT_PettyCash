<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Summary Pengajuan Budget</title>
    <style>
        table { border-collapse: collapse; width: 100%; }
        
        /* No borders for content */
        .no-border { border: none; padding: 8px; }
        
        .header { font-weight: bold; font-size: 14px; }
        .right { text-align: right; }
        .left { text-align: left; }
        
        /* Column widths */
        .col-label { width: 200px; }
        .col-value { width: 150px; }
    </style>
</head>
<body>
    <table>
        <!-- Header -->
        <tr>
            <td colspan="2" class="no-border header">SUMMARY PENGAJUAN BUDGET</td>
        </tr>
        <tr>
            <td colspan="2" class="no-border header">PERIODE {{ $summaryPengajuanBudget->tgl_dari->format('d-M-Y') }} S/D {{ $summaryPengajuanBudget->tgl_sampai->format('d-M-Y') }}</td>
        </tr>
        <tr><td colspan="2" class="no-border"></td></tr>
        <tr><td colspan="2" class="no-border"></td></tr>
        
        <!-- Kas Kecil -->
        <tr>
            <td class="no-border col-label left">KAS KECIL</td>
            <td class="no-border col-value right">{{ number_format($summaryPengajuanBudget->kgs, 2, '.', ',') }}</td>
        </tr>
        
        <!-- Kas Office -->
        <tr>
            <td class="no-border col-label left">KAS OFFICE</td>
            <td class="no-border col-value right">{{ number_format($summaryPengajuanBudget->ogs, 2, '.', ',') }}</td>
        </tr>
        
        <!-- Kas Personalia -->
        <tr>
            <td class="no-border col-label left">KAS PERSONALIA</td>
            <td class="no-border col-value right">{{ number_format($summaryPengajuanBudget->pgs, 2, '.', ',') }}</td>
        </tr>
        
        <!-- Kas Bangunan -->
        <tr>
            <td class="no-border col-label left">KAS GS2</td>
            <td class="no-border col-value right">{{ number_format($summaryPengajuanBudget->bgs, 2, '.', ',') }}</td>
        </tr>
        
        <!-- Total Pengajuan -->
        <tr>
            <td class="no-border col-label left header">TOTAL PENGAJUAN</td>
            <td class="no-border col-value right header">{{ number_format($summaryPengajuanBudget->kgs + $summaryPengajuanBudget->ogs + $summaryPengajuanBudget->pgs + $summaryPengajuanBudget->bgs, 2, '.', ',') }}</td>
        </tr>
        
        <!-- Pembulatan -->
        <tr>
            <td class="no-border col-label left header" style="font-size: 16px;">PEMBULATAN</td>
            <td class="no-border col-value right header" style="font-size: 16px;">{{ number_format($summaryPengajuanBudget->pembulatan, 2, '.', ',') }}</td>
        </tr>
    </table>
</body>
</html>