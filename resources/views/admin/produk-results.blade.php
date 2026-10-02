@php
    $rp = fn ($value) => 'Rp ' . number_format($value, 0, ',', '.');
    $statusBadge = [1 => ['success', 'Aktif'], 0 => ['neutral', 'Nonaktif']];
    $stokBadge = fn ($stok) => $stok <= 0 ? ['danger', 'Habis'] : ($stok <= 10 ? ['warning', 'Menipis ' . $stok] : ['success', $stok]);
@endphp
<div class="table-wrap">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>Produk</th>
                    <th>Kategori</th>
                    <th>Harga</th>
                    <th>Stok</th>
                    <th>Status</th>
                    <th class="text-end">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($products as $product)
                    @php
                        [$stokClass, $stokLabel] = $stokBadge($product->stock);
                        [$statusClass, $statusLabel] = $statusBadge[$product->is_active];
                    @endphp
                    <tr data-cf-row
                        data-name="{{ mb_strtolower(trim($product->name . ' ' . ($product->category?->name ?? '') . ' SKU-' . str_pad($product->id, 3, '0', STR_PAD_LEFT))) }}"
                        data-category="{{ $product->category_id }}"
                        data-status="{{ $product->stock <= 0 ? 'habis' : ($product->stock <= 10 ? 'menipis' : 'tersedia') }}">
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                @if ($product->image)
                                    <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" class="thumb-sm" style="max-width:40px;object-fit:cover;">
                                @else
                                    <span class="thumb-sm"><x-product-placeholder size="sm" :name="$product->name" /></span>
                                @endif
                                <div class="min-w-0">
                                    <div class="fw-semibold text-truncate">{{ $product->name }}</div>
                                    <div class="small text-muted-pos font-monospace">SKU-{{ str_pad($product->id, 3, '0', STR_PAD_LEFT) }}</div>
                                </div>
                            </div>
                        </td>
                        <td><span class="badge badge-soft badge-soft--neutral">{{ $product->category?->name ?? '-' }}</span></td>
                        <td class="fw-semibold text-nowrap">{{ $rp($product->price) }}</td>
                        <td class="text-nowrap">
                            <span class="badge badge-soft badge-soft--{{ $stokClass }}">{{ $stokLabel }}</span>
                        </td>
                        <td>
                            <span class="badge badge-soft badge-soft--{{ $statusClass }}">
                                <span class="badge-soft__dot"></span>{{ $statusLabel }}
                            </span>
                        </td>
                        <td class="text-end text-nowrap">
                            <button type="button" class="btn btn-sm link-secondary py-0" title="Edit produk" data-bs-toggle="modal" data-bs-target="#modalProduk" data-mode="edit" data-url="{{ route('admin.produk.update', ['produk' => $product]) }}" data-payload="{{ json_encode([
                                'name' => $product->name,
                                'description' => $product->description,
                                'price' => $product->price,
                                'stock' => $product->stock,
                                'category_id' => $product->category_id,
                                'is_active' => $product->is_active,
                                'image_url' => $product->image ? asset('storage/' . $product->image) : null,
                            ]) }}">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <form method="POST" action="{{ route('admin.produk.destroy', ['produk' => $product]) }}" style="display:inline;" data-confirm="Hapus produk ini?">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm link-danger py-0" title="Hapus produk">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted-pos py-4">Tidak ada produk ditemukan.</td></tr>
                @endforelse
                <tr data-cf-empty hidden>
                    <td colspan="6" class="text-center text-muted-pos py-4">Tidak ada produk yang cocok dengan filter.</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
<div class="note-box note-box--info mt-3">
    <i class="bi bi-info-circle note-box__icon"></i>
    <span>Stok produk akan otomatis berkurang saat transaksi disetujui.</span>
</div>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mt-4">
    <small class="text-muted-pos" data-cf-summary="Menampilkan {n} produk di halaman ini">Menampilkan {{ $products->firstItem() ?? 0 }}-{{ $products->lastItem() ?? 0 }} dari {{ $products->total() }} produk</small>
    <nav aria-label="Navigasi halaman daftar produk">
        {{ $products->links() }}
    </nav>
</div>
