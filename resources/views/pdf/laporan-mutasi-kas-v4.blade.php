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
            padding: 15px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            border: 2px solid #000;
        }

        th,
        td {
            border: 1px solid #000;
            font-weight: bold;
            font-size: 12px;
            font-family: Arial, sans-serif;
        }

        th {
            background-color: #e0e0e0;
            text-align: center;
        }

        .th-tanggal {
            padding: 2px 2px;
        }

        .th-nomor {
            padding: 3px 0px;
        }

        .th-dari {
            padding: 3px 2px;
        }

        .th-keterangan {
            padding: 3px 2px;
        }

        .th-currency {
            padding: 3px 0px;
        }

        .td-tanggal {
            padding: 2px 0px;
            text-align: center;
        }

        .td-nomor {
            padding: 6px 0px;
            text-align: center;
        }

        .td-dari {
            padding: 6px 7px;
            text-align: left;
        }

        .td-keterangan {
            padding: 6px 7px;
            text-align: left;
        }

        .td-currency {
            padding: 6px 7px;
            text-align: right;
        }

        .td-total {
            background-color: #d0d0d0;
            border-top: 2px solid #000;
            border-bottom: 2px solid #000;
            padding: 6px 7px;
            font-weight: bold;
        }

        .td-total-center {
            text-align: center;
        }

        .td-total-right {
            text-align: right;
        }
    </style>
</head>

<body>
    <div style="text-align: left; margin-bottom: 25px;">
        <h1 style="font-size: 16px; font-weight: bold; margin-bottom: 8px;">LAPORAN MUTASI {{ $jenisKas }} PT. SINGA GLOBAL TEKSTIL</h1>
        <h2 style="font-size: 14px; font-weight: bold; border: 2px solid #000; padding: 5px 8px; display: inline-block;">{{ $periodeText }}</h2>
    </div>

    <table>
        <thead>
            <tr>
                <th class="th-tanggal" style="width: 50px;">TANGGAL</th>
                <th class="th-nomor" style="width: 120px;">NOMOR<br>KM/KK</th>
                <th class="th-dari" style="width: 65px;">DARI/KE</th>
                <th class="th-keterangan" style="width: 165px;">KETERANGAN</th>
                <th class="th-currency" style="width: 40px;">KM</th>
                <th class="th-currency" style="width: 40px;">KK</th>
                <th class="th-currency" style="width: 40px;">SALDO</th>
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
                <td class="td-tanggal" style="width: 50px;">{{ $tanggal }}</td>
                <td class="td-nomor" style="width: 120px;">{{ $row['no_transaksi'] }}</td>
                <td class="td-dari" style="width: 65px;">{{ trim($row['jenis_transaksi']) }}</td>
                <td class="td-keterangan" style="width: 165px;">{{ $keterangan }}</td>
                <td class="td-currency" style="width: 40px;">
                    @if($row['nominal_pemasukan'] > 0)
                    {{ number_format($row['nominal_pemasukan'], 0, '.', ',') }}
                    @else
                    -
                    @endif
                </td>
                <td class="td-currency" style="width: 40px;">
                    @if($row['nominal_pengeluaran'] > 0)
                    {{ number_format($row['nominal_pengeluaran'], 0, '.', ',') }}
                    @else
                    -
                    @endif
                </td>
                <td class="td-currency" style="width: 40px;">{{ number_format($row['saldo_akhir'], 0, '.', ',') }}</td>
            </tr>
            @endforeach

            <tr>
                <td colspan="4" class="td-total td-total-center">TOTAL</td>
                <td class="td-total td-total-right" style="width: 40px;">{{ number_format($totalKM, 0, '.', ',') }}</td>
                <td class="td-total td-total-right" style="width: 40px;">{{ number_format($totalKK, 0, '.', ',') }}</td>
                <td class="td-total td-total-right" style="width: 40px;">{{ number_format($saldoAkhir, 0, '.', ',') }}</td>
            </tr>
        </tbody>
    </table>
</body>

</html>