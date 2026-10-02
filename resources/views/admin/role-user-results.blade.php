@php
    $roleBadge = [
        'admin' => 'badge-soft--warning',
        'kasir' => 'badge-soft--info',
        'pelanggan' => 'badge-soft--success',
    ];
    $roleNames = [
        'admin' => 'Admin',
        'kasir' => 'Kasir',
        'pelanggan' => 'Pelanggan',
    ];
@endphp
<div class="table-wrap">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>User</th>
                    <th>Username</th>
                    <th>Role</th>
                    <th>Terakhir Login</th>
                    <th class="text-end">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($users as $user)
                    <tr data-cf-row data-name="{{ mb_strtolower($user->name . ' ' . $user->email) }}" data-role="{{ $user->role }}">
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <span class="avatar">{{ Str::upper(substr($user->name, 0, 2)) }}</span>
                                <div class="min-w-0">
                                    <div class="fw-semibold text-truncate">{{ $user->name }}</div>
                                    <div class="small text-muted-pos text-truncate">{{ $user->email }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="font-monospace text-nowrap">{{ Str::lower(substr($user->name, 0, 1)) }}{{ substr($user->name, strpos($user->name, ' ') + 1, 1) }}</td>
                        <td>
                            <span class="badge badge-soft {{ $roleBadge[$user->role] }}">
                                {{ $roleNames[$user->role] }}
                            </span>
                        </td>
                        <td class="text-nowrap">{{ $user->updated_at->diffForHumans() }}</td>
                        <td class="text-end text-nowrap">
                            <button type="button" class="btn btn-sm link-secondary py-0" title="Edit user" data-bs-toggle="modal" data-bs-target="#modalUser" data-mode="edit" data-url="{{ route('admin.user-role.update', $user) }}" data-payload="{{ json_encode([
                                'name' => $user->name,
                                'email' => $user->email,
                                'role' => $user->role,
                            ]) }}">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <form method="POST" action="{{ route('admin.user-role.destroy', $user) }}" style="display:inline;" data-confirm="Hapus user ini?">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm link-danger py-0" title="Hapus user">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted-pos py-4">Tidak ada user ditemukan.</td>
                    </tr>
                @endforelse
                <tr data-cf-empty hidden>
                    <td colspan="5" class="text-center text-muted-pos py-4">Tidak ada user yang cocok dengan filter.</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mt-4">
    <small class="text-muted-pos" data-cf-summary="Menampilkan {n} user di halaman ini">Menampilkan {{ $users->firstItem() ?? 0 }}-{{ $users->lastItem() ?? 0 }} dari {{ $users->total() }} user</small>
    <nav aria-label="Navigasi halaman daftar user">
        {{ $users->links() }}
    </nav>
</div>
