@extends('layouts.admin')

@section('title', 'Manajemen User & Role — Admin')
@section('page_title', 'Manajemen User & Role')

@section('content')
    @include('partials.ajax-modal-form')
    @php
        $roleNames = [
            'admin' => 'Admin',
            'kasir' => 'Kasir',
            'pelanggan' => 'Pelanggan',
        ];
        $roleSelects = array_merge(['Semua Role'], array_keys($roleNames));
    @endphp

    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
        <p class="text-muted-pos mb-0 small">Kelola pengguna sistem dan hak akses per role</p>
        <button type="button" class="btn btn-brand" data-bs-toggle="modal" data-bs-target="#modalUser" data-mode="create" data-url="{{ route('admin.user-role.store') }}">
            <i class="bi bi-person-plus me-1"></i> Tambah User
        </button>
    </div>

    <ul class="nav nav-tabs mb-4" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="tab-daftar-user-tab" data-bs-toggle="tab" data-bs-target="#tab-daftar-user" type="button" role="tab" aria-controls="tab-daftar-user" aria-selected="true">
                <i class="bi bi-people me-1"></i> Daftar User
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="tab-role-akses-tab" data-bs-toggle="tab" data-bs-target="#tab-role-akses" type="button" role="tab" aria-controls="tab-role-akses" aria-selected="false">
                <i class="bi bi-shield-lock me-1"></i> Role &amp; Hak Akses
            </button>
        </li>
    </ul>

    <div class="tab-content" id="roleUserTabsContent">
        {{-- ================= TAB 1 — DAFTAR USER ================= --}}
        <div class="tab-pane fade show active" id="tab-daftar-user" role="tabpanel" aria-labelledby="tab-daftar-user-tab">
            <div class="pane mb-4">
                <form class="row g-2 align-items-end" action="{{ route('admin.user-role.index') }}" method="get" data-cf="role-user">
                    <div class="col-12 col-md-4 col-lg-3">
                        <label class="form-label" for="cariUser">Cari User</label>
                        <div class="search-box">
                            <i class="bi bi-search search-box__icon"></i>
                            <input type="search" class="form-control" id="cariUser" data-cf-search placeholder="Cari nama / username..." aria-label="Cari nama atau username" name="search" value="{{ request('search') }}">
                        </div>
                    </div>
                    <div class="col-6 col-md-4 col-lg-3">
                        <label class="form-label" for="filterRole">Role</label>
                        <select class="form-select" id="filterRole" name="role" data-cf-field="role">
                            @foreach ($roleSelects as $role)
                                <option value="{{ $role === 'Semua Role' ? '' : strtolower($role) }}" {{ request('role') == ($role === 'Semua Role' ? '' : strtolower($role)) ? 'selected' : '' }}>{{ $role }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-lg-3">
                        <div class="d-flex flex-wrap gap-2 justify-content-lg-end">
                            <button type="submit" class="btn btn-brand">
                                <i class="bi bi-funnel me-1"></i> Filter
                            </button>
                            <a href="{{ route('admin.user-role.index') }}" class="btn btn-outline-secondary" data-rt-link>
                                <i class="bi bi-arrow-counterclockwise me-1"></i> Reset
                            </a>
                        </div>
                    </div>
                </form>
            </div>

            <div data-rt-results data-cf="role-user">
                @include('admin.role-user-results')
            </div>
        </div>

        {{-- ================= TAB 2 — ROLE & HAK AKSES ================= --}}
        <div class="tab-pane fade" id="tab-role-akses" role="tabpanel" aria-labelledby="tab-role-akses-tab">
            <div class="row g-3 mb-4">
                @foreach ([
                    ['nama' => 'Admin', 'jumlah' => \App\Models\User::where('role', 'admin')->count(), 'icon' => 'bi-shield-lock', 'akses' => ['Kelola semua modul & data', 'Kelola pengguna dan role', 'Akses seluruh laporan'], 'badge' => ''],
                    ['nama' => 'Kasir', 'jumlah' => \App\Models\User::where('role', 'kasir')->count(), 'icon' => 'bi-bag-check', 'akses' => ['Buat transaksi kasir', 'Kelola antrian pesanan', 'Laporan kasir harian & bulanan'], 'badge' => 'badge-soft--info'],
                    ['nama' => 'Pelanggan', 'jumlah' => \App\Models\User::where('role', 'pelanggan')->count(), 'icon' => 'bi-person-badge', 'akses' => ['Lihat katalog produk', 'Buat pesanan online', 'Lacak status pesanan'], 'badge' => 'badge-soft--success'],
                ] as $card)
                    <div class="col-12 col-md-4">
                        <div class="card h-100">
                            <div class="card-body">
                                <div class="d-flex align-items-center gap-2 mb-3">
                                    <span class="avatar avatar--lg"><i class="bi {{ $card['icon'] }}"></i></span>
                                    <div>
                                        <h5 class="card-title h6 mb-0">{{ $card['nama'] }}</h5>
                                        <span class="badge badge-soft {{ $card['badge'] }} mt-1">{{ $card['jumlah'] }} user</span>
                                    </div>
                                </div>
                                <p class="small fw-semibold text-muted-pos mb-2">Akses ringkas</p>
                                <ul class="list-unstyled mb-0">
                                    @foreach ($card['akses'] as $akses)
                                        <li class="small d-flex align-items-start gap-2 mb-1">
                                            <i class="bi bi-check2-circle text-muted-pos"></i>
                                            <span>{{ $akses }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="table-wrap">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Modul &amp; Aksi</th>
                                @foreach ([['nama' => 'Admin', 'badge' => ''], ['nama' => 'Kasir', 'badge' => 'badge-soft--info'], ['nama' => 'Pelanggan', 'badge' => 'badge-soft--success']] as $role)
                                    <th class="text-center text-nowrap">
                                        <span class="badge badge-soft {{ $role['badge'] }}">{{ $role['nama'] }}</span>
                                        <span class="d-block small text-muted-pos fw-normal mt-1">{{ \App\Models\User::where('role', strtolower($role['nama']))->count() }} user</span>
                                    </th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach (['Produk', 'Transaksi', 'Promo', 'User & Role', 'Laporan'] as $modul)
                                @foreach (['Lihat', 'Tambah', 'Edit', 'Hapus'] as $i => $aksi)
                                    <tr>
                                        @if ($i === 0)
                                            <td class="fw-semibold text-nowrap" rowspan="{{ match($modul) { 'Produk' => 4, 'Transaksi' => 3, 'Promo' => 4, default => 2 } }}">{{ $modul }}</td>
                                        @endif
                                        <td class="text-nowrap ps-5">{{ $aksi }}</td>
                                        @php
                                            $roleAccess = match(true) {
                                                $modul === 'Produk' => [true, false, false],
                                                $modul === 'Transaksi' => [true, true, false],
                                                $modul === 'Promo' => [true, false, false],
                                                $modul === 'User & Role' => [true, false, false],
                                                $modul === 'Laporan' => [true, true, false],
                                                default => [false, false, false],
                                            };
                                        @endphp
                                        @foreach ($roleAccess as $checked)
                                            <td class="text-center">
                                                <input class="form-check-input mt-0" type="checkbox" {{ $checked ? 'checked' : '' }} aria-label="{{ $modul }} {{ $aksi }}" disabled>
                                            </td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="note-box note-box--warning mt-4">
                <i class="bi bi-exclamation-triangle note-box__icon"></i>
                <span>
                    Perubahan hak akses di halaman ini bersifat <strong>tampilan</strong> &mdash; belum terhubung ke database.
                </span>
            </div>
        </div>
    </div>

    {{-- ================= MODAL USER (TAMBAH / EDIT) ================= --}}
    <div id="modalUser" class="modal fade" tabindex="-1" aria-labelledby="modalUserLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form id="formUser" method="POST" action="{{ route('admin.user-role.store') }}">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalUserLabel">User</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label" for="userName">Nama Lengkap</label>
                                <input type="text" class="form-control" id="userName" name="name" required>
                                <div class="invalid-feedback" data-error-for="name"></div>
                            </div>
                            <div class="col-12">
                                <label class="form-label" for="userEmail">Email</label>
                                <input type="email" class="form-control" id="userEmail" name="email" required>
                                <div class="invalid-feedback" data-error-for="email"></div>
                            </div>
                            <div class="col-12">
                                <label class="form-label" for="userRole">Role</label>
                                <select class="form-select" id="userRole" name="role" required>
                                    <option value="">-- Pilih Role --</option>
                                    <option value="admin">Admin</option>
                                    <option value="kasir">Kasir</option>
                                    <option value="pelanggan">Pelanggan</option>
                                </select>
                                <div class="invalid-feedback" data-error-for="role"></div>
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label" for="userPassword">Password</label>
                                <input type="password" class="form-control" id="userPassword" name="password" minlength="8" placeholder="Min. 8 karakter">
                                <div class="invalid-feedback" data-error-for="password"></div>
                                <div class="form-text" id="userPasswordHint">Minimal 8 karakter</div>
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label" for="userPasswordConfirmation">Konfirmasi Password</label>
                                <input type="password" class="form-control" id="userPasswordConfirmation" name="password_confirmation" placeholder="Ulangi password">
                                <div class="invalid-feedback" data-error-for="password_confirmation"></div>
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

    {{-- ================= MODAL TAUTAN RESET PASSWORD ================= --}}
    <div id="modalResetLink" class="modal fade" tabindex="-1" aria-labelledby="modalResetLinkLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalResetLinkLabel">Tautan Reset Password</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body">
                    <p class="small mb-3">
                        Salin tautan di bawah lalu kirimkan manual ke
                        <strong id="resetLinkEmail">user</strong> (mis. lewat WhatsApp).
                        Tautan hanya berlaku sekali dan kedaluwarsa setelah
                        <strong id="resetLinkExpiry">60 menit</strong>.
                    </p>
                    <div class="input-group">
                        <input type="text" id="resetLinkUrl" class="form-control font-monospace" readonly>
                        <button type="button" class="btn btn-brand" id="resetLinkCopy">
                            <i class="bi bi-clipboard me-1"></i> Salin
                        </button>
                    </div>
                    <div class="form-text">
                        Membuat tautan baru akan membatalkan tautan sebelumnya untuk user yang sama.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var userForm = document.getElementById('formUser');
    var userModal = document.getElementById('modalUser');

    if (!userForm || !userModal) {
        return;
    }

    var userTitle = document.getElementById('modalUserLabel');
    var userPassword = document.getElementById('userPassword');
    var userPasswordConfirmation = document.getElementById('userPasswordConfirmation');
    var userPasswordHint = document.getElementById('userPasswordHint');
    var userRole = document.getElementById('userRole');
    var originalRole = '';

    userModal.addEventListener('show.bs.modal', function (event) {
        var trigger = event.relatedTarget;
        if (!trigger) {
            return;
        }

        POSModalForm.open(userForm, trigger, function (context) {
            var isEdit = context.mode === 'edit';

            userTitle.textContent = isEdit ? 'Edit User' : 'Tambah User';
            userPassword.required = !isEdit;
            userPasswordConfirmation.required = !isEdit;
            userPassword.placeholder = isEdit ? 'Biarkan kosong jika tidak diganti' : 'Min. 8 karakter';
            userPasswordConfirmation.placeholder = isEdit ? 'Ulangi password baru' : 'Ulangi password';
            userPasswordHint.textContent = isEdit ? 'Kosongkan jika tidak ingin mengubah password' : 'Minimal 8 karakter';
            originalRole = isEdit && context.payload ? String(context.payload.role || '') : '';
        });
    });

    userForm.addEventListener('submit', function (event) {
        event.preventDefault();

        if (userForm.dataset.mode === 'edit' && userRole.value && userRole.value !== originalRole) {
            Swal.fire({
                title: 'Konfirmasi Ubah Role',
                text: 'Ubah role user menjadi ' + userRole.options[userRole.selectedIndex].text + '?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, ubah!',
                cancelButtonText: 'Batal',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    originalRole = userRole.value;
                    POSModalForm.submit(userForm);
                }
            });

            return;
        }

        POSModalForm.submit(userForm);
    });
});

/* Tautan reset password dibuat manual oleh admin. Tombolnya ada di partial
   tabel yang di-render ulang saat filter berubah, jadi event-nya didelegasikan
   ke document, bukan diikat langsung ke tombol. */

/* Clipboard API hanya tersedia di secure context, sedangkan outlet sering
   diakses lewat http:// — jadi ada fallback ke execCommand. */
function copyText(value) {
    if (navigator.clipboard && window.isSecureContext) {
        return navigator.clipboard.writeText(value).then(function () {
            return true;
        }).catch(function () {
            return copyTextFallback(value);
        });
    }

    return Promise.resolve(copyTextFallback(value));
}

function copyTextFallback(value) {
    var field = document.createElement('textarea');

    field.value = value;
    field.setAttribute('readonly', '');
    field.style.position = 'fixed';
    field.style.opacity = '0';
    document.body.appendChild(field);
    field.select();

    var copied = false;

    try {
        copied = document.execCommand('copy');
    } catch (error) {
        copied = false;
    }

    document.body.removeChild(field);

    return copied;
}

document.addEventListener('click', function (event) {
    var trigger = event.target.closest('[data-reset-link]');

    if (!trigger) {
        return;
    }

    event.preventDefault();

    var url = trigger.getAttribute('data-url');
    var email = trigger.getAttribute('data-email') || '';

    var modalElement = document.getElementById('modalResetLink');
    var urlField = document.getElementById('resetLinkUrl');
    var emailLabel = document.getElementById('resetLinkEmail');
    var expiryLabel = document.getElementById('resetLinkExpiry');

    if (!modalElement || !urlField) {
        return;
    }

    Swal.fire({
        title: 'Buat Tautan Reset Password?',
        text: 'Tautan untuk ' + email + ' akan dibuat dan harus dikirim manual ke user.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Ya, buat!',
        cancelButtonText: 'Batal',
        reverseButtons: true
    }).then(function (result) {
        if (!result.isConfirmed) {
            return;
        }

        Swal.fire({
            title: 'Membuat tautan...',
            allowOutsideClick: false,
            didOpen: function () {
                Swal.showLoading();
            }
        });

        fetch(url, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
                'X-Requested-With': 'XMLHttpRequest',
                Accept: 'application/json'
            }
        })
            .then(function (response) {
                return response.json().then(function (data) {
                    return { ok: response.ok, data: data };
                });
            })
            .then(function (result) {
                if (!result.ok) {
                    throw new Error(result.data.message || 'Gagal membuat tautan reset password.');
                }

                Swal.close();

                urlField.value = result.data.url;
                emailLabel.textContent = result.data.email || email;
                expiryLabel.textContent = result.data.expires_in_minutes + ' menit';

                bootstrap.Modal.getOrCreateInstance(modalElement).show();
            })
            .catch(function (error) {
                Swal.close();
                Swal.fire({
                    title: 'Gagal',
                    text: error.message,
                    icon: 'error',
                    confirmButtonText: 'Tutup'
                });
            });
    });
});

document.addEventListener('click', function (event) {
    var copyButton = event.target.closest('#resetLinkCopy');

    if (!copyButton) {
        return;
    }

    event.preventDefault();

    var urlField = document.getElementById('resetLinkUrl');

    if (!urlField || !urlField.value) {
        return;
    }

    copyText(urlField.value).then(function (copied) {
        Swal.fire({
            title: copied ? 'Tautan tersalin' : 'Gagal menyalin',
            text: copied ? '' : 'Salin manual dari kolom tautan.',
            icon: copied ? 'success' : 'warning',
            timer: copied ? 1500 : null,
            showConfirmButton: !copied
        });
    });
});
</script>
@endpush