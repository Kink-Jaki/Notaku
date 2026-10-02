@extends('layouts.admin')
@section('title', 'Manajemen Produk — Admin')
@section('page_title', 'Manajemen Produk')
@section('content')
    @include('partials.ajax-modal-form')
    @php
        $kategoriFilter = ['Semua Kategori'] + $categories->pluck('name', 'id')->toArray();
        $stokFilter = ['' => 'Semua', 'menipis' => 'Stok Menipis', 'habis' => 'Habis'];
    @endphp
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
        <p class="text-muted-pos mb-0 small">Input, edit, kategori &amp; stok produk</p>
        <button type="button" class="btn btn-brand" data-bs-toggle="modal" data-bs-target="#modalProduk" data-mode="create" data-url="{{ route('admin.produk.store') }}">
            <i class="bi bi-plus-lg me-1"></i> Tambah Produk
        </button>
    </div>
    <ul class="nav nav-tabs mb-4" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="tab-daftar-produk-tab" data-bs-toggle="tab" data-bs-target="#tab-daftar-produk" type="button" role="tab" aria-controls="tab-daftar-produk" aria-selected="true">
                <i class="bi bi-box-seam me-1"></i> Daftar Produk
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="tab-kategori-tab" data-bs-toggle="tab" data-bs-target="#tab-kategori" type="button" role="tab" aria-controls="tab-kategori" aria-selected="false">
                <i class="bi bi-tags me-1"></i> Kategori
            </button>
        </li>
    </ul>
    <div class="tab-content" id="produkTabsContent">
        {{-- ================= TAB 1 — DAFTAR PRODUK ================= --}}
        <div class="tab-pane fade show active" id="tab-daftar-produk" role="tabpanel" aria-labelledby="tab-daftar-produk-tab">
            <form class="pane mb-4" action="{{ route('admin.produk.index') }}" method="get" data-cf="produk">
                <div class="row g-2 align-items-end">
                    <div class="col-12 col-md-4 col-lg-3">
                        <label class="form-label" for="cariProduk">Cari Produk</label>
                        <div class="search-box">
                            <i class="bi bi-search search-box__icon"></i>
                            <input type="search" class="form-control" id="cariProduk" name="search" data-cf-search placeholder="Cari nama / kategori..." aria-label="Cari nama atau kategori" value="{{ request('search') }}">
                        </div>
                    </div>
                    <div class="col-6 col-md-4 col-lg-3">
                        <label class="form-label" for="filterKategoriProduk">Kategori</label>
                        <select class="form-select" id="filterKategoriProduk" name="category_id" data-cf-field="category">
                            @foreach ($kategoriFilter as $id => $nama)
                                <option value="{{ $id }}" {{ request('category_id') == $id ? 'selected' : '' }}>{{ $nama }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-6 col-md-4 col-lg-3">
                        <label class="form-label" for="filterStokProduk">Stok</label>
                        <select class="form-select" id="filterStokProduk" name="status" data-cf-field="status">
                            @foreach ($stokFilter as $nilai => $nama)
                                <option value="{{ $nilai }}" {{ request('status') == $nilai ? 'selected' : '' }}>{{ $nama }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-lg-2">
                        <div class="d-flex flex-wrap gap-2 justify-content-lg-end">
                            <button type="submit" class="btn btn-brand">
                                <i class="bi bi-funnel me-1"></i> Filter
                            </button>
                            <a href="{{ route('admin.produk.index') }}" class="btn btn-outline-secondary" data-rt-link>
                                <i class="bi bi-arrow-counterclockwise me-1"></i> Reset
                            </a>
                        </div>
                    </div>
                </div>
            </form>
            <div data-rt-results data-cf="produk">
                @include('admin.produk-results')
            </div>
        </div>
        {{-- ================= TAB 2 — KATEGORI ================= --}}<div class="tab-pane fade" id="tab-kategori" role="tabpanel" aria-labelledby="tab-kategori-tab">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                    <div>
                        <h2 class="h5 mb-0">Kategori Produk</h2>
                        <p class="small text-muted-pos mb-0">Kelompokkan produk agar mudah dicari dan difilter</p>
                    </div>
                    <a href="{{ route('admin.kategori.index') }}" class="btn btn-brand btn-sm">
                        <i class="bi bi-tags me-1"></i> Kelola Kategori
                    </a>
                </div>
                <div class="row g-3">
                @foreach ($categories as $kategori)
                    <div class="col-12 col-sm-6 col-lg-3">
                        <div class="card h-100">
                            <div class="card-body d-flex flex-column">
                                <div class="d-flex align-items-center gap-3 mb-3">
                                    <span class="avatar avatar--lg"><i class="bi bi-tag"></i></span>
                                    <div class="min-w-0">
                                        <h3 class="card-title h6 mb-0">{{ $kategori->name }}</h3>
                                        <span class="badge badge-soft badge-soft--neutral mt-1">{{ $kategori->products_count }} produk</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
    {{-- ================= MODAL PRODUK (TAMBAH / EDIT) ================= --}}
    <div id="modalProduk" class="modal fade" tabindex="-1" aria-labelledby="modalProdukLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <form id="formProduk" method="POST" action="{{ route('admin.produk.store') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalProdukLabel">Produk</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label" for="produkName">Nama Produk</label>
                                <input type="text" class="form-control" id="produkName" name="name" maxlength="255" required>
                                <div class="invalid-feedback" data-error-for="name"></div>
                            </div>
                            <div class="col-12">
                                <label class="form-label" for="produkDescription">Deskripsi</label>
                                <textarea class="form-control" id="produkDescription" name="description" rows="3"></textarea>
                                <div class="invalid-feedback" data-error-for="description"></div>
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label" for="produkPrice">Harga (Rp)</label>
                                <input type="number" class="form-control" id="produkPrice" name="price" min="0" required>
                                <div class="invalid-feedback" data-error-for="price"></div>
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label" for="produkStock">Stok</label>
                                <input type="number" class="form-control" id="produkStock" name="stock" min="0" required>
                                <div class="invalid-feedback" data-error-for="stock"></div>
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label" for="produkCategory">Kategori</label>
                                <select class="form-select" id="produkCategory" name="category_id" required>
                                    <option value="">-- Pilih Kategori --</option>
                                    @foreach ($categories as $category)
                                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                                    @endforeach
                                </select>
                                <div class="invalid-feedback" data-error-for="category_id"></div>
                            </div>
                            <div class="col-12 col-md-6">
                                <input type="hidden" name="is_active" value="0">
                                <div class="form-check mt-4">
                                    <input class="form-check-input" type="checkbox" id="produkIsActive" name="is_active" value="1" checked>
                                    <label class="form-check-label" for="produkIsActive">Aktif</label>
                                </div>
                            </div>
                            <div class="col-12">
                                <label class="form-label" for="produkImage">Gambar Produk</label>
                                <div class="mb-2 d-none" id="produkImagePreview">
                                    <img src="" alt="Pratinjau gambar" class="img-thumbnail" style="max-width: 150px;">
                                    <div class="form-check mt-2">
                                        <input class="form-check-input" type="checkbox" id="produkRemoveImage" name="remove_image" value="1">
                                        <label class="form-check-label text-danger" for="produkRemoveImage">Hapus gambar ini</label>
                                    </div>
                                </div>
                                <input type="file" class="form-control" id="produkImage" name="image" accept="image/*">
                                <div class="invalid-feedback" data-error-for="image"></div>
                                <div class="form-text">Maksimal 2MB. Format: JPG, PNG, WebP. Kosongkan untuk mempertahankan gambar lama.</div>
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
    var produkForm = document.getElementById('formProduk');
    var produkModal = document.getElementById('modalProduk');

    if (!produkForm || !produkModal) {
        return;
    }

    var produkTitle = document.getElementById('modalProdukLabel');
    var produkPreview = document.getElementById('produkImagePreview');

    produkModal.addEventListener('show.bs.modal', function (event) {
        var trigger = event.relatedTarget;
        if (!trigger) {
            return;
        }

        POSModalForm.open(produkForm, trigger, function (context) {
            produkTitle.textContent = context.mode === 'edit' ? 'Edit Produk' : 'Tambah Produk';
            produkPreview.classList.add('d-none');

            if (context.mode === 'edit' && context.payload && context.payload.image_url) {
                produkPreview.querySelector('img').src = context.payload.image_url;
                produkPreview.classList.remove('d-none');
            }
        });
    });

    produkForm.addEventListener('submit', function (event) {
        event.preventDefault();
        POSModalForm.submit(produkForm);
    });
});
</script>
@endpush
