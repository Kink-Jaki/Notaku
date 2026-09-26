@php
    $swalLoginSuccess = session('login_success');
    $swalLogoutSuccess = session('logout_success');
    $swalSuccess = session('swal_success');
    $swalWarning = session('swal_warning');
    $swalError = session('swal_error');
    $swalCartSuccess = session('cart_success');
    $swalFallbackSuccess = session('success');
    $swalFallbackError = session('error');
    $swalStatus = session('status');
    $swalStatusText = match ($swalStatus) {
        'verification-link-sent' => 'Tautan verifikasi baru telah dikirim ke email Anda.',
        'password-updated' => 'Kata sandi berhasil diperbarui.',
        'profile-updated' => 'Profil berhasil diperbarui.',
        'We have emailed your password reset link.' => 'Kami telah mengirimkan tautan reset password ke email Anda.',
        'Your password has been reset.' => 'Kata sandi Anda berhasil diatur ulang.',
        default => $swalStatus,
    };
    $swalValidationErrors = $errors->all();
    $swalValidationText = count($swalValidationErrors) > 1
        ? implode(' ', array_map(fn ($message) => '- '.$message, $swalValidationErrors))
        : ($swalValidationErrors[0] ?? '');
@endphp

@push('scripts')
<script>
    (function () {
        function showFlashNotification() {
            if (typeof Swal === 'undefined') {
                return;
            }

            @if ($swalLoginSuccess)
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil Login',
                    text: @json($swalLoginSuccess),
                    timer: 2500,
                    showConfirmButton: false,
                });
            @elseif ($swalLogoutSuccess)
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil Keluar',
                    text: @json($swalLogoutSuccess),
                    timer: 2500,
                    showConfirmButton: false,
                });
            @elseif ($swalSuccess)
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil',
                    text: @json($swalSuccess),
                    timer: 2500,
                    showConfirmButton: false,
                });
            @elseif ($swalWarning)
                Swal.fire({
                    icon: 'warning',
                    title: 'Perhatian',
                    text: @json($swalWarning),
                    confirmButtonText: 'Mengerti',
                });
            @elseif ($swalError)
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: @json($swalError),
                });
            @elseif ($swalCartSuccess)
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'success',
                    title: @json($swalCartSuccess),
                    showConfirmButton: false,
                    timer: 2000,
                    timerProgressBar: true,
                });
            @elseif ($swalFallbackSuccess)
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil',
                    text: @json($swalFallbackSuccess),
                    timer: 2500,
                    showConfirmButton: false,
                });
            @elseif ($swalFallbackError)
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: @json($swalFallbackError),
                });
            @elseif ($swalStatusText)
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil',
                    text: @json($swalStatusText),
                    timer: 2500,
                    showConfirmButton: false,
                });
            @elseif ($swalValidationText !== '')
                Swal.fire({
                    icon: 'error',
                    title: 'Validasi Gagal',
                    text: @json($swalValidationText),
                });
            @endif
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', showFlashNotification);
        } else {
            showFlashNotification();
        }
    })();
</script>
@endpush
