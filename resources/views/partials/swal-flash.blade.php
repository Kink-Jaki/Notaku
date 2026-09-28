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

    // Get settings for theme colors
    $settings = \App\Support\SettingsHelper::get();
@endphp

@push('scripts')
<script>
    (function () {
        function showFlashNotification() {
            if (typeof Swal === 'undefined') {
                return;
            }

            // Theme colors from settings
            const themeColors = {
                primary: @json($settings->color_primary),
                primaryDark: @json($settings->color_primary_dark),
                secondary: @json($settings->color_secondary),
                success: @json($settings->color_success),
                warning: @json($settings->color_warning),
                danger: @json($settings->color_danger),
            };

            // Common Swal options with theme colors
            const swalOptions = {
                background: themeColors.primaryDark,
                color: '#FFFFFF',
                confirmButtonColor: themeColors.primary,
                cancelButtonColor: themeColors.secondary,
                denyButtonColor: themeColors.danger,
                buttonsStyling: true,
            };

            @if ($swalLoginSuccess)
                Swal.fire({
                    ...swalOptions,
                    icon: 'success',
                    title: 'Berhasil Login',
                    text: @json($swalLoginSuccess),
                    timer: 2500,
                    showConfirmButton: false,
                });
            @elseif ($swalLogoutSuccess)
                Swal.fire({
                    ...swalOptions,
                    icon: 'success',
                    title: 'Berhasil Keluar',
                    text: @json($swalLogoutSuccess),
                    timer: 2500,
                    showConfirmButton: false,
                });
            @elseif ($swalSuccess)
                Swal.fire({
                    ...swalOptions,
                    icon: 'success',
                    title: 'Berhasil',
                    text: @json($swalSuccess),
                    timer: 2500,
                    showConfirmButton: false,
                });
            @elseif ($swalWarning)
                Swal.fire({
                    ...swalOptions,
                    icon: 'warning',
                    title: 'Perhatian',
                    text: @json($swalWarning),
                    confirmButtonText: 'Mengerti',
                });
            @elseif ($swalError)
                Swal.fire({
                    ...swalOptions,
                    icon: 'error',
                    title: 'Gagal',
                    text: @json($swalError),
                });
            @elseif ($swalCartSuccess)
                Swal.fire({
                    ...swalOptions,
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
                    ...swalOptions,
                    icon: 'success',
                    title: 'Berhasil',
                    text: @json($swalFallbackSuccess),
                    timer: 2500,
                    showConfirmButton: false,
                });
            @elseif ($swalFallbackError)
                Swal.fire({
                    ...swalOptions,
                    icon: 'error',
                    title: 'Gagal',
                    text: @json($swalFallbackError),
                });
            @elseif ($swalStatusText)
                Swal.fire({
                    ...swalOptions,
                    icon: 'success',
                    title: 'Berhasil',
                    text: @json($swalStatusText),
                    timer: 2500,
                    showConfirmButton: false,
                });
            @elseif ($swalValidationText !== '')
                Swal.fire({
                    ...swalOptions,
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
