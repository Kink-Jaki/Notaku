@extends('layouts.admin')
@section('title', 'Kode Promo — Admin')
@section('page_title', 'Kode Promo')
@section('content')
    @include('partials.ajax-modal-form')
    @php
        $statusSelects = ['' => 'Semua', 'active' => 'Aktif', 'inactive' => 'Nonaktif'];
        $jenisSelects = ['' => 'Semua', 'percent' => 'Persen', 'fixed' => 'Nominal'];
    @endphp
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
        <p class="text-muted-pos mb-0 small">Promo diskon/kuota &amp; masa berlaku — diterapkan manual saat checkout</p>
        <button type="button" class="btn btn-brand" data-bs-toggle="modal" data-bs-target="#modalPromo" data-mode="create" data-url="{{ route('admin.promo.store') }}">
            <i class="bi bi-plus-lg me-1"></i> Buat Promo
        </button>
    </div>
    <div class="pane mb-4">
        <form class="row g-2 align-items-end" action="{{ route('admin.promo.index') }}" method="get" data-cf="promo">
            <div class="col-12 col-md-4 col-lg-3">
                <label class="form-label" for="cariKodePromo">Cari Kode Promo</label>
                <div class="search-box">
                    <i class="bi bi-search search-box__icon"></i>
                    <input type="search" class="form-control" id="cariKodePromo" name="search" data-cf-search placeholder="Cari kode promo..." aria-label="Cari kode promo" value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-6 col-md-4 col-lg-3">
                <label class="form-label" for="filterStatusPromo">Status</label>
                <select class="form-select" id="filterStatusPromo" name="status" data-cf-field="status">
                    @foreach ($statusSelects as $nilai => $label)
                        <option value="{{ $nilai }}" {{ request('status', '') == $nilai ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-4 col-lg-3">
                <label class="form-label" for="filterJenisPromo">Jenis</label>
                <select class="form-select" id="filterJenisPromo" name="jenis" data-cf-field="jenis">
                    @foreach ($jenisSelects as $nilai => $label)
                        <option value="{{ $nilai }}" {{ request('jenis', '') == $nilai ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-12 col-lg-2">
                <div class="d-flex flex-wrap gap-2 justify-content-lg-end">
                    <button type="submit" class="btn btn-brand">
                        <i class="bi bi-funnel me-1"></i> Filter
                    </button>
                    <a href="{{ route('admin.promo.index') }}" class="btn btn-outline-secondary" data-rt-link>
                        <i class="bi bi-arrow-counterclockwise me-1"></i> Reset
                    </a>
                </div>
            </div>
        </form>
    </div>
    <div data-rt-results data-cf="promo">
        @include('admin.promo-results')
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
