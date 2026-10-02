@extends(match (auth()->user()->role) {
    'kasir' => 'layouts.kasir',
    'pelanggan' => 'layouts.pelanggan',
    default => 'layouts.admin',
})

@section('title', 'Profil Saya')

@section('content')
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

    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
        <div>
            <h1 class="h4 mb-1">Profil Saya</h1>
            <p class="small text-muted-pos mb-0">Kelola data diri, kata sandi, dan akun kamu</p>
        </div>
        <span class="badge badge-soft {{ $roleBadge[$user->role] ?? 'badge-soft--neutral' }}">
            {{ $roleNames[$user->role] ?? ucfirst($user->role) }}
        </span>
    </div>

    <div class="row g-3 align-items-start">
        <div class="col-12 col-lg-7">
            <div class="pane mb-4">
                <h2 class="h6 fw-semibold mb-3">Informasi Akun</h2>
                <form method="POST" action="{{ route('profile.update') }}">
                    @csrf
                    @method('PATCH')
                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label" for="name">Nama Lengkap</label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $user->name) }}" required maxlength="255">
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label" for="email">Email</label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email', $user->email) }}" required maxlength="255">
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Mengganti email akan menonaktifkan status verifikasi sampai email baru dikonfirmasi</div>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-brand mt-4">
                        <i class="bi bi-save me-1"></i> Simpan Perubahan
                    </button>
                </form>
            </div>

            <div class="pane mb-4">
                <h2 class="h6 fw-semibold mb-3">Ubah Kata Sandi</h2>
                <form method="POST" action="{{ route('password.update') }}">
                    @csrf
                    @method('PUT')
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label" for="current_password">Kata Sandi Saat Ini</label>
                            <input type="password" class="form-control @error('current_password', 'updatePassword') is-invalid @enderror" id="current_password" name="current_password" required>
                            @error('current_password', 'updatePassword')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label" for="password">Kata Sandi Baru</label>
                            <input type="password" class="form-control @error('password', 'updatePassword') is-invalid @enderror" id="password" name="password" required minlength="8">
                            @error('password', 'updatePassword')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Min. 8 karakter</div>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label" for="password_confirmation">Ulangi Kata Sandi Baru</label>
                            <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" required minlength="8">
                        </div>
                    </div>
                    <button type="submit" class="btn btn-brand mt-4">
                        <i class="bi bi-key me-1"></i> Perbarui Kata Sandi
                    </button>
                </form>
            </div>
        </div>

        <div class="col-12 col-lg-5">
            <div class="pane mb-4">
                <h2 class="h6 fw-semibold mb-3">Detail Akun</h2>
                <ul class="list-unstyled mb-0 small">
                    <li class="d-flex justify-content-between gap-3 py-2 border-bottom">
                        <span class="text-muted-pos">Nama</span>
                        <span class="fw-semibold text-end">{{ $user->name }}</span>
                    </li>
                    <li class="d-flex justify-content-between gap-3 py-2 border-bottom">
                        <span class="text-muted-pos">Email</span>
                        <span class="fw-semibold text-end">{{ $user->email }}</span>
                    </li>
                    <li class="d-flex justify-content-between gap-3 py-2 border-bottom">
                        <span class="text-muted-pos">Role</span>
                        <span class="fw-semibold text-end">{{ $roleNames[$user->role] ?? ucfirst($user->role) }}</span>
                    </li>
                    <li class="d-flex justify-content-between gap-3 py-2">
                        <span class="text-muted-pos">Terakhir diperbarui</span>
                        <span class="fw-semibold text-end">{{ $user->updated_at?->diffForHumans() ?? '-' }}</span>
                    </li>
                </ul>
            </div>

            <div class="pane mb-4">
                <h2 class="h6 fw-semibold mb-3">Hapus Akun</h2>
                <p class="small text-muted-pos">Akun beserta datanya akan dihapus permanen dan tidak bisa dikembalikan.</p>
                <form method="POST" action="{{ route('profile.destroy') }}" data-confirm="Hapus akun kamu secara permanen?">
                    @csrf
                    @method('DELETE')
                    <div class="row g-2 align-items-end">
                        <div class="col-12 col-sm-7">
                            <label class="form-label" for="delete_password">Konfirmasi Kata Sandi</label>
                            <input type="password" class="form-control @error('password', 'userDeletion') is-invalid @enderror" id="delete_password" name="password" required>
                            @error('password', 'userDeletion')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-12 col-sm-5">
                            <button type="submit" class="btn btn-outline-danger w-100">
                                <i class="bi bi-trash me-1"></i> Hapus Akun
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
