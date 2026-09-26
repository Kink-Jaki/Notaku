@extends('layouts.admin')
@section('title', 'Kode Promo — Admin')
@section('page_title', 'Kode Promo')
@section('content')
    @include('partials.ajax-modal-form')
    @php
        $statusBadge = ['aktif' => 'success', 'nonaktif' => 'neutral', 'kedaluwarsa' => 'warning'];
        $jenisBadge = ['percent' => 'info', 'fixed' => 'success'];
        $statusSelects = ['Semua', 'Aktif', 'Nonaktif'];
        $jenisSelects = ['Semua', 'Persen', 'Nominal'];
    @endphp
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
        <p class="text-muted-pos mb-0 small">Promo diskon/kuota &amp; masa berlaku — diterapkan manual saat checkout</p>
        <button type="button" class="btn btn-brand" data-bs-toggle="modal" data-bs-target="#modalPromo" data-mode="create" data-url="{{ route('admin.promo.store') }}">
            <i class="bi bi-plus-lg me-1"></i> Buat Promo
        </button>
    </div>
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
    <div class="pane mb-4">
        <form class="row g-2 align-items-end" action="{{ route('admin.promo.index') }}" method="get">
            <div class="col-12 col-md-4 col-lg-3">
                <label class="form-label" for="cariKodePromo">Cari Kode Promo</label>
                <div class="search-box">
                    <i class="bi bi-search search-box__icon"></i>
                    <input type="search" class="form-control" id="cariKodePromo" name="search" placeholder="Cari kode promo..." aria-label="Cari kode promo" value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-6 col-md-4 col-lg-3">
                <label class="form-label" for="filterStatusPromo">Status</label>
                <select class="form-select" id="filterStatusPromo" name="status">
                    @foreach ($statusSelects as $status)
                        <option value="{{ $status }}" {{ request('status') == $status ? 'selected' : '' }}>{{ $status }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-4 col-lg-3">
                <label class="form-label" for="filterJenisPromo">Jenis</label>
                <select class="form-select" id="filterJenisPromo" name="jenis">
                    @foreach ($jenisSelects as $jenis)
                        <option value="{{ strtolower($jenis) }}" {{ request('jenis') == strtolower($jenis) ? 'selected' : '' }}>{{ $jenis }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-12 col-lg-2">
                <div class="d-flex flex-wrap gap-2 justify-content-lg-end">
                    <button type="submit" class="btn btn-brand">
                        <i class="bi bi-funnel me-1"></i> Filter
                    </button>
                    <a href="{{ route('admin.promo.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-counterclockwise me-1"></i> Reset
                    </a>
                </div>
            </div>
        </form>
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
                        <tr>
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
                </tbody>
            </table>
        </div>
    </div>
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mt-4">
        <small class="text-muted-pos">Menampilkan {{ $promos->firstItem() ?? 0 }}-{{ $promos->lastItem() ?? 0 }} dari {{ $promos->total() }} promo</small>
        <nav aria-label="Navigasi halaman daftar kode promo">
            {{ $promos->links() }}
        </nav>
    </div>
    {{-- ================= MODAL PROMO (TAMBAH / EDIT) ================= --}}
    <div id="modalPromo" class="modal fade" tabindex="-1" aria-labelledby="modalPromoLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <form id="formPromo" method="POST" action="{{ route('admin.promo.store') }}">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalPromoLabel">Promo</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-12 col-md-6">
                                <label class="form-label" for="promoCode">Kode Promo</label>
                                <input type="text" class="form-control" id="promoCode" name="code" maxlength="50" style="text-transform: uppercase;" required>
                                <div class="invalid-feedback" data-error-for="code"></div>
                                <div class="form-text">Akan otomatis diubah ke huruf besar</div>
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label" for="promoType">Tipe Diskon</label>
                                <select class="form-select" id="promoType" name="type" required>
                                    <option value="">-- Pilih Tipe --</option>
                                    <option value="percent">Persentase (%)</option>
                                    <option value="fixed">Nominal Tetap (Rp)</option>
                                </select>
                                <div class="invalid-feedback" data-error-for="type"></div>
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label" for="promoValue">Nilai Diskon</label>
                                <input type="number" class="form-control" id="promoValue" name="value" min="1" required>
                                <div class="invalid-feedback" data-error-for="value"></div>
                                <div class="form-text">Persentase: 1-100. Nominal: Rp</div>
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label" for="promoMinOrder">Minimum Pemesanan (Rp)</label>
                                <input type="number" class="form-control" id="promoMinOrder" name="min_order" min="0" value="0" required>
                                <div class="invalid-feedback" data-error-for="min_order"></div>
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label" for="promoUsageLimit">Batas Penggunaan</label>
                                <input type="number" class="form-control" id="promoUsageLimit" name="usage_limit" min="1">
                                <div class="invalid-feedback" data-error-for="usage_limit"></div>
                                <div class="form-text">Kosongkan untuk tidak dibatasi</div>
                            </div>
                            <div class="col-12 col-md-6">
                                <input type="hidden" name="is_active" value="0">
                                <div class="form-check mt-4">
                                    <input class="form-check-input" type="checkbox" id="promoIsActive" name="is_active" value="1" checked>
                                    <label class="form-check-label" for="promoIsActive">Aktif</label>
                                </div>
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label" for="promoStartsAt">Mulai Berlaku</label>
                                <input type="date" class="form-control" id="promoStartsAt" name="starts_at">
                                <div class="invalid-feedback" data-error-for="starts_at"></div>
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label" for="promoEndsAt">Berakhir</label>
                                <input type="date" class="form-control" id="promoEndsAt" name="ends_at">
                                <div class="invalid-feedback" data-error-for="ends_at"></div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-brand" data-submit>
                            <i class="bi bi-save me-1"></i> Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('form[data-confirm]').forEach(function (form) {
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            Swal.fire({
                title: 'Konfirmasi',
                text: this.getAttribute('data-confirm'),
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, hapus!',
                cancelButtonText: 'Batal',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    this.submit();
                }
            });
        });
    });

    var promoForm = document.getElementById('formPromo');
    var promoModal = document.getElementById('modalPromo');

    if (!promoForm || !promoModal) {
        return;
    }

    var promoTitle = document.getElementById('modalPromoLabel');

    promoModal.addEventListener('show.bs.modal', function (event) {
        var trigger = event.relatedTarget;
        if (!trigger) {
            return;
        }

        POSModalForm.open(promoForm, trigger, function (context) {
            promoTitle.textContent = context.mode === 'edit' ? 'Edit Promo' : 'Buat Promo';
        });
    });

    promoForm.addEventListener('submit', function (event) {
        event.preventDefault();
        POSModalForm.submit(promoForm);
    });
});
</script>
@endpush
