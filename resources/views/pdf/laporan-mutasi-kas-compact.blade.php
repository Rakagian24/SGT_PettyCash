<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Laporan Mutasi Kas {{ $jenisKas }}</title>
    <!-- VERSI 3.0 HEADER COMPACT {{ now()->timestamp }} -->
    <style>
        /* CSS Version 3.0 COMPACT HEADER - {{ now()->timestamp }} */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 13px;
            line-height: 1.2;
            color: #000;
            padding: 15px;
            font-weight: bold;
        }

        .header {
            text-align: left;
            margin-bottom: 25px;
        }

        .header h1 {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 8px;
        }

        .header h2 {
            font-size: 14px;
            font-weight: bold;
            border: 2px solid #000;
            padding: 5px 8px;
            display: inline-block;
            margin-bottom: 15px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
            table-layout: fixed;
            border: 2px solid #000;
            font-family: Arial, sans-serif;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 6px 7px;
            text-align: left;
            vertical-align: middle;
            word-wrap: break-word;
            overflow: hidden;
            font-weight: bold;
            font-size: 12px;
            font-family: Arial, sans-serif;
        }

        /* Padding khusus untuk kolom tanggal agar lebih rapat */
        .col-tanggal {
            padding: 3px 0px !important;
            font-size: 11px !important;
        }

        th {
            background-color: #e0e0e0;
            font-weight: bold;
            text-align: center;
            font-size: 12px;
            border: 1px solid #000;
            font-family: Arial, sans-serif;
            padding: 3px 2px !important;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .currency {
            text-align: right;
            font-family: Arial, sans-serif;
            font-weight: bold;
            font-size: 12px;
        }

        .total-row {
            font-weight: bold;
            background-color: #d0d0d0;
            border-top: 2px solid #000;
            border-bottom: 2px solid #000;
            font-size: 12px;
        }

        .keterangan {
            max-width: 260px;
            word-wrap: break-word;
            font-size: 12px;
            line-height: 1.2;
            overflow: hidden;
            font-weight: bold;
            font-family: Arial, sans-serif;
        }

        .no-wrap {
            white-space: nowrap;
        }

        /* Kolom width untuk portrait - disesuaikan agar pas A4 */
        .col-tanggal {
            width: 55px !important;
        }

        .col-nomor {
            width: 95px;
        }

        .col-dari {
            width: 65px;
        }

        .col-keterangan {
            width: 250px;
        }

        .col-km {
            width: 65px;
        }

        .col-kk {
            width: 65px;
        }

        .col-saldo {
            width: 65px;
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>LAPORAN MUTASI {{ $jenisKas }} PT. SINGA GLOBAL TEKSTIL</h1>
        <h2>{{ $periodeText }}</h2>
    </div>

    <table>
        <thead>
            <tr>
                <th class="col-tanggal" style="width: 55px; padding: 3px 0px !important; font-size: 11px;">TANGGAL</th>
                <th class="col-nomor" style="width: 95px; padding: 3px 2px !important;">NOMOR<br>KM/KK</th>
                <th class="col-dari" style="width: 65px; padding: 3px 2px !important;">DARI/KE</th>
                <th class="col-keterangan" style="width: 250px; padding: 3px 2px !important;">KETERANGAN</th>
                <th class="col-km" style="width: 65px; padding: 3px 2px !important;">KM</th>
                <th class="col-kk" style="width: 65px; padding: 3px 2px !important;">KK</th>
                <th class="col-saldo" style="width: 65px; padding: 3px 2px !important;">SALDO</th>
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
            $tanggal = \Carbon\Carbon::parse($tanggal)->format('d.m.Y');
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
                <td class="text-center no-wrap" style="width: 55px; padding: 3px 0px; font-size: 11px;">{{ $tanggal }}</td>
                <td class="text-center no-wrap" style="width: 95px;">{{ $row['no_transaksi'] }}</td>
                <td style="width: 65px;">{{ trim($row['jenis_transaksi']) }}</td>
                <td class="keterangan" style="width: 250px;">{{ $keterangan }}</td>
                <td class="currency" style="width: 65px;">
                    @if($row['nominal_pemasukan'] > 0)
                    {{ number_format($row['nominal_pemasukan'], 2, '.', ',') }}
                    @else
                    -
                    @endif
                </td>
                <td class="currency" style="width: 65px;">
                    @if($row['nominal_pengeluaran'] > 0)
                    {{ number_format($row['nominal_pengeluaran'], 2, '.', ',') }}
                    @else
                    -
                    @endif
                </td>
                <td class="currency" style="width: 65px;">{{ number_format($row['saldo_akhir'], 2, '.', ',') }}</td>
            </tr>
            @endforeach

            <!-- Baris Total -->
            <tr class="total-row">
                <td colspan="4" class="text-center" style="width: 465px;"><strong>TOTAL</strong></td>
                <td class="currency" style="width: 65px;"><strong>{{ number_format($totalKM, 2, '.', ',') }}</strong></td>
                <td class="currency" style="width: 65px;"><strong>{{ number_format($totalKK, 2, '.', ',') }}</strong></td>
                <td class="currency" style="width: 65px;"><strong>{{ number_format($saldoAkhir, 2, '.', ',') }}</strong></td>
            </tr>
        </tbody>
    </table>
</body>

</html>