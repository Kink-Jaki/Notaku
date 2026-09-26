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
        <form class="row g-2 align-items-end" action="{{ route('admin.kategori.index') }}" method="get">
            <div class="col-12 col-md-4 col-lg-3">
                <label class="form-label" for="cariKategori">Cari Kategori</label>
                <div class="search-box">
                    <i class="bi bi-search search-box__icon"></i>
                    <input type="search" class="form-control" id="cariKategori" name="search" placeholder="Cari nama kategori..." aria-label="Cari nama kategori" value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-12 col-lg-2">
                <div class="d-flex flex-wrap gap-2 justify-content-lg-end">
                    <button type="submit" class="btn btn-brand">
                        <i class="bi bi-funnel me-1"></i> Filter
                    </button>
                    <a href="{{ route('admin.kategori.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-counterclockwise me-1"></i> Reset
                    </a>
                </div>
            </div>
        </form>
    </div>
    <div class="table-wrap">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>Kategori</th>
                        <th>Slug</th>
                        <th>Jumlah Produk</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($categories as $kategori)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="avatar avatar--sm"><i class="bi bi-tag"></i></span>
                                    <span class="fw-semibold">{{ $kategori->name }}</span>
                                </div>
                            </td>
                            <td><span class="font-monospace small">{{ $kategori->slug }}</span></td>
                            <td>{{ $kategori->products_count }}</td>
                            <td class="text-end text-nowrap">
                                <button type="button" class="btn btn-sm link-secondary py-0" title="Edit kategori" data-bs-toggle="modal" data-bs-target="#modalKategori" data-mode="edit" data-url="{{ route('admin.kategori.update', $kategori) }}" data-payload="{{ json_encode([
                                    'name' => $kategori->name,
                                    'slug' => $kategori->slug,
                                ]) }}">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <form method="POST" action="{{ route('admin.kategori.destroy', $kategori) }}" style="display:inline;" data-confirm="Hapus kategori ini?">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm link-danger py-0" title="Hapus kategori">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center text-muted-pos py-4">Tidak ada kategori ditemukan.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mt-4">
        <small class="text-muted-pos">Menampilkan {{ $categories->firstItem() ?? 0 }}-{{ $categories->lastItem() ?? 0 }} dari {{ $categories->total() }} kategori</small>
        <nav aria-label="Navigasi halaman daftar kategori">
            {{ $categories->links() }}
        </nav>
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
