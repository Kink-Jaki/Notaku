<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Laporan Bulanan - {{ $labelBulan }}</title>
    <style>
        body { font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif; font-size: 11px; line-height: 1.4; color: #212529; padding: 20px; }
        .header { text-align: center; margin-bottom: 20px; }
        .header h1 { font-size: 18px; font-weight: 700; margin: 0 0 5px; color: #4f46e5; }
        .header p { margin: 0; font-size: 13px; color: #6c757d; }
        .info-row { display: flex; justify-content: space-between; margin-bottom: 15px; }
        .info-row div { font-size: 12px; }
        .info-label { font-weight: 600; color: #495057; }
        .info-value { color: #212529; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { border: 1px solid #dee2e6; padding: 6px 8px; text-align: left; font-size: 11px; }
        th { background-color: #f8f9fa; font-weight: 600; color: #495057; }
        .text-end { text-align: right; }
        .text-nowrap { white-space: nowrap; }
        .fw-semibold { font-weight: 600; }
        .text-muted-pos { color: #6c757d; }
        .badge { display: inline-block; padding: 2px 6px; border-radius: 10px; font-size: 10px; font-weight: 600; }
        .badge-success { background-color: #d1e7dd; color: #0f5132; }
        .badge-info { background-color: #cff4fc; color: #055160; }
        .badge-warning { background-color: #fff3cd; color: #664d03; }
        .badge-neutral { background-color: #e2e3e5; color: #383d41; }
        .badge-primary { background-color: #cfe2ff; color: #084298; }
        .badge-danger { background-color: #f8d7da; color: #842029; }
        .fw-bold { font-weight: 700; }
        .total-row { background-color: #f8f9fa; font-weight: 700; }
        .stat-row { display: flex; justify-content: space-between; margin-bottom: 5px; font-size: 12px; }
        .stat-label { color: #6c757d; }
        .stat-value { font-weight: 600; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Notaku</h1>
        <p>Laporan Penjualan Bulanan - {{ $labelBulan }}</p>
    </div>

    <div class="info-row">
        <div><span class="info-label">Tanggal Cetak: </span><span class="info-value">{{ now()->translatedFormat('l, d F Y H:i') }}</span></div>
        <div><span class="info-label">Bulan: </span><span class="info-value">{{ $labelBulan }}</span></div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Tanggal</th>
                <th class="text-end">Transaksi</th>
                <th class="text-end">Item</th>
                <th class="text-end">Kasir Manual</th>
                <th class="text-end">Online</th>
                <th class="text-end">Diskon</th>
                <th class="text-end">Total</th>
            </thead>
            <tbody>
                @foreach ($rekap as $baris)
                    <tr>
                        <td class="text-nowrap">{{ $baris['hari'] }} {{ $shortBulan }}</td>
                        <td class="text-end text-nowrap">{{ number_format($baris['trx'], 0, ',', '.') }}</td>
                        <td class="text-end text-nowrap">{{ number_format($baris['item'], 0, ',', '.') }}</td>
                        <td class="text-end text-nowrap">Rp {{ number_format($baris['manual'], 0, ',', '.') }}</td>
                        <td class="text-end text-nowrap">Rp {{ number_format($baris['online'], 0, ',', '.') }}</td>
                        <td class="text-end text-nowrap text-muted-pos">−Rp {{ number_format($baris['diskon'], 0, ',', '.') }}</td>
                        <td class="text-end text-nowrap fw-semibold">Rp {{ number_format($baris['total'], 0, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="total-row">
                    <td>Total</td>
                    <td class="text-end text-nowrap">{{ number_format($grandTrx, 0, ',', '.') }}</td>
                    <td class="text-end text-nowrap">{{ number_format($grandItem, 0, ',', '.') }}</td>
                    <td class="text-end text-nowrap">Rp {{ number_format($grandManual, 0, ',', '.') }}</td>
                    <td class="text-end text-nowrap">Rp {{ number_format($grandOnline, 0, ',', '.') }}</td>
                    <td class="text-end text-nowrap text-muted-pos">−Rp {{ number_format($grandDiskon, 0, ',', '.') }}</td>
                    <td class="text-end text-nowrap fw-bold">Rp {{ number_format($grandTotal, 0, ',', '.') }}</td>
                </tr>
            </tfoot>
        </table>

    <div style="margin-top: 20px;">
        <h4 style="font-size: 14px; margin-bottom: 10px;">Ringkasan</h4>
        <div class="stat-row"><span class="stat-label">Total Transaksi:</span><span class="stat-value">{{ number_format($grandTrx, 0, ',', '.') }} transaksi</span></div>
        <div class="stat-row"><span class="stat-label">Total Item Terjual:</span><span class="stat-value">{{ number_format($grandItem, 0, ',', '.') }} item</span></div>
        <div class="stat-row"><span class="stat-label">Penjualan Kasir Manual:</span><span class="stat-value">Rp {{ number_format($grandManual, 0, ',', '.') }}</span></div>
        <div class="stat-row"><span class="stat-label">Penjualan Online (approved):</span><span class="stat-value">Rp {{ number_format($grandOnline, 0, ',', '.') }}</span></div>
        <div class="stat-row"><span class="stat-label">Total Diskon:</span><span class="stat-value">Rp {{ number_format($grandDiskon, 0, ',', '.') }}</span></div>
        <div class="stat-row"><span class="stat-label">Rata-rata per Hari:</span><span class="stat-value">Rp {{ number_format($rataHari, 0, ',', '.') }}</span></div>
        <div class="stat-row"><span class="stat-label">Total Penjualan:</span><span class="stat-value fw-bold">Rp {{ number_format($grandTotal, 0, ',', '.') }}</span></div>
    </div>

    <div style="margin-top: 20px; text-align: center; font-size: 10px; color: #6c757d;">
        Laporan di-generate otomatis pada {{ now()->translatedFormat('l, d F Y H:i') }} | Notaku System
    </div>
</body>
</html>