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
                    <tr data-cf-row data-name="{{ mb_strtolower($kategori->name . ' ' . $kategori->slug) }}">
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
                <tr data-cf-empty hidden>
                    <td colspan="4" class="text-center text-muted-pos py-4">Tidak ada kategori yang cocok dengan filter.</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mt-4">
    <small class="text-muted-pos" data-cf-summary="Menampilkan {n} kategori di halaman ini">Menampilkan {{ $categories->firstItem() ?? 0 }}-{{ $categories->lastItem() ?? 0 }} dari {{ $categories->total() }} kategori</small>
    <nav aria-label="Navigasi halaman daftar kategori">
        {{ $categories->links() }}
    </nav>
</div>
