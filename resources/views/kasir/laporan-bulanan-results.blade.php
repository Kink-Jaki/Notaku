    <div class="row g-3 mb-4">
        @foreach ($statCards as $stat)
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="stat-card stat-card--{{ $stat['modifier'] }}">
                    <div>
                        <div class="stat-card__label">{{ $stat['label'] }}</div>
                        <div class="stat-card__value text-truncate" title="{{ $stat['value'] }}">{{ $stat['value'] }}</div>
                    </div>
                    <div class="stat-card__icon">
                        <i class="bi {{ $stat['icon'] }}"></i>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="pane pane--flush mb-4">
        <div class="pane__header px-3 pt-3 pb-0">
            <h2 class="pane__title h5">Rekap per Tanggal</h2>
            <span class="badge badge-soft badge-soft--neutral">{{ $labelBulan }}</span>
        </div>

        <div class="table-wrap mt-3">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th class="text-end">Transaksi</th>
                            <th class="text-end">Item Terjual</th>
                            <th class="text-end">Kasir Manual</th>
                            <th class="text-end">Online (approved)</th>
                            <th class="text-end">Diskon</th>
                            <th class="text-end">Total Penjualan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($rekap as $baris)
                            <tr class="{{ $baris['hari'] === $tglHariIni ? 'table-active' : '' }}">
                                <td class="text-nowrap">
                                    {{ $baris['hari'] }} {{ $shortBulan }}
                                    @if ($baris['hari'] === $tglHariIni)
                                        <span class="badge badge-soft badge-soft--neutral ms-1">&middot; hari ini</span>
                                    @endif
                                </td>
                                <td class="text-end text-nowrap">{{ $fmt($baris['trx']) }}</td>
                                <td class="text-end text-nowrap">{{ $fmt($baris['item']) }}</td>
                                <td class="text-end text-nowrap">{{ $rp($baris['manual']) }}</td>
                                <td class="text-end text-nowrap">{{ $rp($baris['online']) }}</td>
                                <td class="text-end text-nowrap text-muted-pos">−{{ $rp($baris['diskon']) }}</td>
                                <td class="text-end fw-semibold text-nowrap">{{ $rp($baris['total']) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="fw-semibold">
                            <td class="border-top">Total</td>
                            <td class="border-top text-end text-nowrap">{{ $fmt($grandTrx) }}</td>
                            <td class="border-top text-end text-nowrap">{{ $fmt($grandItem) }}</td>
                            <td class="border-top text-end text-nowrap">{{ $rp($grandManual) }}</td>
                            <td class="border-top text-end text-nowrap">{{ $rp($grandOnline) }}</td>
                            <td class="border-top text-end text-nowrap text-muted-pos">−{{ $rp($grandDiskon) }}</td>
                            <td class="border-top text-end text-nowrap">{{ $rp($grandTotal) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    <div class="pane">
        <div class="pane__header">
            <h2 class="pane__title h5">Kategori Produk Terlaris</h2>
            <span class="badge badge-soft badge-soft--neutral">{{ $labelBulan }}</span>
        </div>

        <div class="table-responsive">
            <table class="table table-borderless align-middle mb-0">
                <thead>
                    <tr>
                        <th>Kategori</th>
                        <th class="text-end">Penjualan</th>
                        <th class="text-end">Bagian</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($topKategori as $kategori)
                        @php $bagian = $grandTotal > 0 ? round(($kategori['total'] / $grandTotal) * 100) : 0; @endphp
                        <tr>
                            <td>
                                <i class="bi {{ $kategori['icon'] }} me-2"></i>{{ $kategori['nama'] }}
                            </td>
                            <td class="text-end text-nowrap">
                                <span class="fw-semibold">{{ $rp($kategori['total']) }}</span>
                                <span class="small text-muted-pos d-block">± {{ number_format($kategori['total'] / 1000000, 1, ',', '.') }} jt</span>
                            </td>
                            <td class="text-end">
                                <span class="badge badge-soft badge-soft--{{ $kategori['badge'] }}">{{ $bagian }}%</span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="fw-semibold">
                        <td class="border-top">Total</td>
                        <td class="border-top text-end text-nowrap">{{ $rp($grandTotal) }}</td>
                        <td class="border-top text-end">100%</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
