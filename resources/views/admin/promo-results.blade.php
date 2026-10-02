@php
    $statusBadge = ['aktif' => 'success', 'nonaktif' => 'neutral', 'kedaluwarsa' => 'warning'];
    $jenisBadge = ['percent' => 'info', 'fixed' => 'success'];
@endphp
<div class="row g-3 mb-4 row-cols-1 row-cols-md-3">
    <div class="col">
        <div class="stat-card stat-card--success h-100">
            <div>
                <div class="stat-card__label">Promo Aktif</div>
                <div class="stat-card__value">{{ $promos->where('is_active', true)->count() }}</div>
            </div>
            <div class="stat-card__icon"><i class="bi bi-ticket-perforated"></i></div>
        </div>
    </div>
    <div class="col">
        <div class="stat-card stat-card--warning h-100">
            <div>
                <div class="stat-card__label">Nonaktif</div>
                <div class="stat-card__value">{{ $promos->where('is_active', false)->count() }}</div>
            </div>
            <div class="stat-card__icon"><i class="bi bi-hourglass-split"></i></div>
        </div>
    </div>
    <div class="col">
        <div class="stat-card stat-card--neutral h-100">
            <div>
                <div class="stat-card__label">Total Promo</div>
                <div class="stat-card__value">{{ $promos->total() }}</div>
            </div>
            <div class="stat-card__icon"><i class="bi bi-cash-stack"></i></div>
        </div>
    </div>
</div>
<div class="d-flex justify-content-end mb-3">
    <div class="note-box note-box--info">
        <i class="bi bi-info-circle note-box__icon"></i>
        <span>Kode promo dipakai dengan mengetik kode manual di halaman Kasir/POS.</span>
    </div>
</div>
<div class="table-wrap">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>Kode</th>
                    <th>Deskripsi</th>
                    <th>Jenis</th>
                    <th>Nilai</th>
                    <th>Masa Berlaku</th>
                    <th>Kuota Terpakai</th>
                    <th>Status</th>
                    <th class="text-end">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($promos as $promo)
                    @php
                        $status = !$promo->is_active
                            ? 'nonaktif'
                            : ($promo->ends_at && \Carbon\Carbon::parse($promo->ends_at)->isPast() ? 'kedaluwarsa' : 'aktif');
                        $persenKuota = $promo->usage_limit > 0
                            ? round(($promo->used_count / $promo->usage_limit) * 100)
                            : 0;
                        $mulai = \Carbon\Carbon::parse($promo->starts_at)->translatedFormat('d M Y');
                        $selesai = \Carbon\Carbon::parse($promo->ends_at)->translatedFormat('d M Y');
                        $jenisLabel = $promo->type === 'percent' ? 'Persen' : 'Nominal';
                        $nilaiLabel = $promo->type === 'percent' ? $promo->value . '%' : 'Rp ' . number_format($promo->value, 0, ',', '.');
                    @endphp
                    <tr data-cf-row data-name="{{ mb_strtolower($promo->code . ' ' . ($promo->description ?? '')) }}" data-status="{{ $promo->is_active ? 'active' : 'inactive' }}" data-jenis="{{ $promo->type }}">
                        <td><span class="font-monospace fw-semibold text-nowrap">{{ $promo->code }}</span></td>
                        <td><span class="d-inline-block text-truncate truncate-xs">{{ $promo->description ?? '-' }}</span></td>
                        <td><span class="badge badge-soft badge-soft--{{ $jenisBadge[$promo->type] }}">{{ $jenisLabel }}</span></td>
                        <td class="fw-semibold text-nowrap">{{ $nilaiLabel }}</td>
                        <td class="text-nowrap small">{{ $mulai }} &ndash; {{ $selesai }}</td>
                        <td class="text-nowrap">
                            <span class="me-2">{{ $promo->used_count }} dari {{ $promo->usage_limit ?? '-' }}</span>
                            @if ($promo->usage_limit > 0)
                                <span class="badge badge-soft badge-soft--neutral">{{ $persenKuota }}%</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge badge-soft badge-soft--{{ $statusBadge[$status] }}">
                                <span class="badge-soft__dot"></span>{{ ucfirst($status) }}
                            </span>
                        </td>
                        <td class="text-end text-nowrap">
                            <button type="button" class="btn btn-sm link-secondary py-0" title="Edit promo" data-bs-toggle="modal" data-bs-target="#modalPromo" data-mode="edit" data-url="{{ route('admin.promo.update', $promo) }}" data-payload="{{ json_encode([
                                'code' => $promo->code,
                                'type' => $promo->type,
                                'value' => $promo->value,
                                'min_order' => $promo->min_order,
                                'usage_limit' => $promo->usage_limit,
                                'is_active' => $promo->is_active,
                                'starts_at' => $promo->starts_at?->format('Y-m-d'),
                                'ends_at' => $promo->ends_at?->format('Y-m-d'),
                            ]) }}">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <form method="POST" action="{{ route('admin.promo.destroy', $promo) }}" style="display:inline;" data-confirm="Hapus promo ini?">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm link-danger py-0" title="Hapus promo">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="text-center text-muted-pos py-4">Tidak ada promo ditemukan.</td></tr>
                @endforelse
                <tr data-cf-empty hidden>
                    <td colspan="8" class="text-center text-muted-pos py-4">Tidak ada promo yang cocok dengan filter.</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mt-4">
    <small class="text-muted-pos" data-cf-summary="Menampilkan {n} promo di halaman ini">Menampilkan {{ $promos->firstItem() ?? 0 }}-{{ $promos->lastItem() ?? 0 }} dari {{ $promos->total() }} promo</small>
    <nav aria-label="Navigasi halaman daftar kode promo">
        {{ $promos->links() }}
    </nav>
</div>
