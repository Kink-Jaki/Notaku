@extends('layouts.admin')
@section('title', 'Kategori — Admin')
@section('page_title', 'Kategori Produk')
@section('content')
    @include('partials.ajax-modal-form')
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
        <p class="text-muted-pos mb-0 small">Kelola kategori produk untuk pengelompokan</p>
        <button type="button" class="btn btn-brand" data-bs-toggle="modal" data-bs-target="#modalKategori" data-mode="create" data-url="{{ route('admin.kategori.store') }}">
            <i class="bi bi-plus-lg me-1"></i> Tambah Kategori
        </button>
    </div>
    <div class="pane mb-4">
        <form class="row g-2 align-items-end" action="{{ route('admin.kategori.index') }}" method="get" data-cf="kategori">
            <div class="col-12 col-md-4 col-lg-3">
                <label class="form-label" for="cariKategori">Cari Kategori</label>
                <div class="search-box">
                    <i class="bi bi-search search-box__icon"></i>
                    <input type="search" class="form-control" id="cariKategori" name="search" data-cf-search placeholder="Cari nama kategori..." aria-label="Cari nama kategori" value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-12 col-lg-2">
                <div class="d-flex flex-wrap gap-2 justify-content-lg-end">
                    <button type="submit" class="btn btn-brand">
                        <i class="bi bi-funnel me-1"></i> Filter
                    </button>
                    <a href="{{ route('admin.kategori.index') }}" class="btn btn-outline-secondary" data-rt-link>
                        <i class="bi bi-arrow-counterclockwise me-1"></i> Reset
                    </a>
                </div>
            </div>
        </form>
    </div>
    <div data-rt-results data-cf="kategori">
        @include('admin.kategori-results')
    </div>
    {{-- ================= MODAL KATEGORI (TAMBAH / EDIT) ================= --}}
    <div id="modalKategori" class="modal fade" tabindex="-1" aria-labelledby="modalKategoriLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form id="formKategori" method="POST" action="{{ route('admin.kategori.store') }}">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalKategoriLabel">Kategori</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label" for="kategoriName">Nama Kategori</label>
                                <input type="text" class="form-control" id="kategoriName" name="name" maxlength="255" required>
                                <div class="invalid-feedback" data-error-for="name"></div>
                            </div>
                            <div class="col-12">
                                <label class="form-label" for="kategoriSlug">Slug</label>
                                <input type="text" class="form-control" id="kategoriSlug" name="slug" maxlength="255" required>
                                <div class="invalid-feedback" data-error-for="slug"></div>
                                <div class="form-text">URL-friendly version dari nama kategori (contoh: makanan-minuman)</div>
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
    var kategoriForm = document.getElementById('formKategori');
    var kategoriModal = document.getElementById('modalKategori');

    if (!kategoriForm || !kategoriModal) {
        return;
    }

    var kategoriTitle = document.getElementById('modalKategoriLabel');

    kategoriModal.addEventListener('show.bs.modal', function (event) {
        var trigger = event.relatedTarget;
        if (!trigger) {
            return;
        }

        POSModalForm.open(kategoriForm, trigger, function (context) {
            kategoriTitle.textContent = context.mode === 'edit' ? 'Edit Kategori' : 'Tambah Kategori';
        });
    });

    kategoriForm.addEventListener('submit', function (event) {
        event.preventDefault();
        POSModalForm.submit(kategoriForm);
    });
});
</script>
@endpush
