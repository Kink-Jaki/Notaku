@extends('layouts.admin')

@section('title', 'Developer — Admin')

@section('page_title', 'Pengaturan Developer')

@section('content')
<div class="row g-3">
    <div class="col-12 col-xl-8">
        <x-card>
            <div class="card-header">
                <h2 class="card-title h5 mb-0">Branding & Tema</h2>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.developer.update') }}" method="POST" enctype="multipart/form-data" id="developer-form">
                    @csrf

                    {{-- Nama Brand --}}
                    <div class="mb-3">
                        <label for="brand_name" class="form-label fw-semibold">Nama Brand</label>
                        <input type="text" class="form-control" id="brand_name" name="brand_name" value="{{ $settings->brand_name }}" required maxlength="50">
                        <div class="form-text">Nama yang ditampilkan di navbar, title browser, dan footer.</div>
                    </div>

                    {{-- Logo --}}
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Logo <span class="text-muted small">(PNG/JPG/SVG, max 2MB)</span></label>
                        <div class="d-flex align-items-center gap-3 flex-wrap">
                            @if ($settings->logo_path)
                                <img src="{{ asset('storage/'.$settings->logo_path) }}" alt="{{ $settings->brand_name }} Logo" class="border rounded" style="max-height: 60px; max-width: 200px;" id="logo-preview">
                            @else
                                <div class="border rounded d-flex align-items-center justify-content-center bg-light" style="height: 60px; width: 200px;" id="logo-preview-placeholder">
                                    <span class="text-muted small">Belum ada logo</span>
                                </div>
                            @endif
                            <input type="file" class="form-control" id="logo" name="logo" accept="image/png,image/jpeg,image/svg+xml" style="max-width: 300px;">
                        </div>
                    </div>

                    {{-- Favicon --}}
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Favicon <span class="text-muted small">(PNG/ICO, max 512KB)</span></label>
                        <div class="d-flex align-items-center gap-3 flex-wrap">
                            @if ($settings->favicon_path)
                                <img src="{{ asset('storage/'.$settings->favicon_path) }}" alt="{{ $settings->brand_name }} Favicon" class="border rounded" style="width: 32px; height: 32px;" id="favicon-preview">
                            @else
                                <div class="border rounded d-flex align-items-center justify-content-center bg-light" style="width: 32px; height: 32px;" id="favicon-preview-placeholder">
                                    <i class="bi bi-image text-muted"></i>
                                </div>
                            @endif
                            <input type="file" class="form-control" id="favicon" name="favicon" accept="image/png,image/x-icon" style="max-width: 300px;">
                        </div>
                    </div>

                    <hr class="my-4">

                    {{-- Warna Tema (hanya tampil saat Mode Developer aktif) --}}
                    <div id="color-pickers-section" data-color-row style="display: {{ $settings->dev_mode ? 'block' : 'none' }};">
                        <h3 class="h6 fw-semibold mb-3">Warna Tema <span class="text-muted small">(Mode Developer aktif)</span></h3>
                        <div class="row g-3">
                            <div class="col-12 col-md-6">
                                <label for="color_primary" class="form-label fw-semibold">Primary</label>
                                <div class="d-flex align-items-center gap-2">
                                    <input type="color" class="form-control form-control-color" id="color_primary" name="color_primary" value="{{ $settings->color_primary }}" data-preview="primary">
                                    <input type="text" class="form-control form-control-sm text-uppercase" value="{{ $settings->color_primary }}" readonly id="color_primary_hex">
                                </div>
                            </div>
                            <div class="col-12 col-md-6">
                                <label for="color_primary_dark" class="form-label fw-semibold">Primary Dark</label>
                                <div class="d-flex align-items-center gap-2">
                                    <input type="color" class="form-control form-control-color" id="color_primary_dark" name="color_primary_dark" value="{{ $settings->color_primary_dark }}" data-preview="primary-dark">
                                    <input type="text" class="form-control form-control-sm text-uppercase" value="{{ $settings->color_primary_dark }}" readonly id="color_primary_dark_hex">
                                </div>
                            </div>
                            <div class="col-12 col-md-6">
                                <label for="color_secondary" class="form-label fw-semibold">Secondary</label>
                                <div class="d-flex align-items-center gap-2">
                                    <input type="color" class="form-control form-control-color" id="color_secondary" name="color_secondary" value="{{ $settings->color_secondary }}" data-preview="secondary">
                                    <input type="text" class="form-control form-control-sm text-uppercase" value="{{ $settings->color_secondary }}" readonly id="color_secondary_hex">
                                </div>
                            </div>
                            <div class="col-12 col-md-6">
                                <label for="color_secondary_dark" class="form-label fw-semibold">Secondary Dark</label>
                                <div class="d-flex align-items-center gap-2">
                                    <input type="color" class="form-control form-control-color" id="color_secondary_dark" name="color_secondary_dark" value="{{ $settings->color_secondary_dark }}" data-preview="secondary-dark">
                                    <input type="text" class="form-control form-control-sm text-uppercase" value="{{ $settings->color_secondary_dark }}" readonly id="color_secondary_dark_hex">
                                </div>
                            </div>
                            <div class="col-12 col-md-6">
                                <label for="color_success" class="form-label fw-semibold">Success</label>
                                <div class="d-flex align-items-center gap-2">
                                    <input type="color" class="form-control form-control-color" id="color_success" name="color_success" value="{{ $settings->color_success }}" data-preview="success">
                                    <input type="text" class="form-control form-control-sm text-uppercase" value="{{ $settings->color_success }}" readonly id="color_success_hex">
                                </div>
                            </div>
                            <div class="col-12 col-md-6">
                                <label for="color_warning" class="form-label fw-semibold">Warning</label>
                                <div class="d-flex align-items-center gap-2">
                                    <input type="color" class="form-control form-control-color" id="color_warning" name="color_warning" value="{{ $settings->color_warning }}" data-preview="warning">
                                    <input type="text" class="form-control form-control-sm text-uppercase" value="{{ $settings->color_warning }}" readonly id="color_warning_hex">
                                </div>
                            </div>
                            <div class="col-12 col-md-6">
                                <label for="color_danger" class="form-label fw-semibold">Danger</label>
                                <div class="d-flex align-items-center gap-2">
                                    <input type="color" class="form-control form-control-color" id="color_danger" name="color_danger" value="{{ $settings->color_danger }}" data-preview="danger">
                                    <input type="text" class="form-control form-control-sm text-uppercase" value="{{ $settings->color_danger }}" readonly id="color_danger_hex">
                                </div>
                            </div>
                        </div>
                    </div>

                    <hr class="my-4">

                    {{-- Template Varian & Developer Mode --}}
                    <h3 class="h6 fw-semibold mb-3">Template Varian & Mode Developer</h3>
                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <label for="theme_variant" class="form-label fw-semibold">Template Varian (Preset Warna)</label>
                            <select class="form-select" id="theme_variant" name="theme_variant">
                                @foreach ($presetOptions as $key => $name)
                                    <option value="{{ $key }}" {{ $settings->theme_variant === $key ? 'selected' : '' }}>{{ $name }}</option>
                                @endforeach
                            </select>
                            <div class="form-text">Pilih preset warna siap pakai. Warna akan otomatis terisi sesuai preset yang dipilih.</div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="form-check form-switch mt-4">
                                <input class="form-check-input" type="checkbox" id="dev_mode" name="dev_mode" {{ $settings->dev_mode ? 'checked' : '' }}>
                                <label class="form-check-label fw-semibold" for="dev_mode">Mode Developer (Advance)</label>
                            </div>
                            <div class="form-text">Aktifkan untuk menyesuaikan warna secara manual (override preset).</div>
                        </div>
                    </div>

                    <hr class="my-4">

                    {{-- Media Sosial & Kontak --}}
                    <h3 class="h6 fw-semibold mb-3">Media Sosial & Kontak</h3>
                    <div class="row g-3">
                        <div class="col-12">
                            <label for="footer_tagline" class="form-label fw-semibold">Tagline Footer</label>
                            <textarea class="form-control" id="footer_tagline" name="footer_tagline" rows="2" maxlength="200" placeholder="Deskripsi singkat brand untuk footer, misal: Solusi kasir modern untuk usaha F&B & sembako kamu">{{ $settings->footer_tagline ?? '' }}</textarea>
                            <div class="form-text">Teks pendek yang ditampilkan di kolom brand footer.</div>
                        </div>
                        <div class="col-12 col-md-6">
                            <label for="social_instagram" class="form-label fw-semibold">Instagram</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-instagram"></i></span>
                                <input type="url" class="form-control" id="social_instagram" name="social_instagram" value="{{ $settings->social_instagram ?? '' }}" placeholder="https://instagram.com/namaakun" maxlength="255">
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <label for="social_facebook" class="form-label fw-semibold">Facebook</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-facebook"></i></span>
                                <input type="url" class="form-control" id="social_facebook" name="social_facebook" value="{{ $settings->social_facebook ?? '' }}" placeholder="https://facebook.com/namaakun" maxlength="255">
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <label for="social_tiktok" class="form-label fw-semibold">TikTok</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-tiktok"></i></span>
                                <input type="url" class="form-control" id="social_tiktok" name="social_tiktok" value="{{ $settings->social_tiktok ?? '' }}" placeholder="https://tiktok.com/@namaakun" maxlength="255">
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <label for="social_whatsapp" class="form-label fw-semibold">WhatsApp</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-whatsapp"></i></span>
                                <input type="text" class="form-control" id="social_whatsapp" name="social_whatsapp" value="{{ $settings->social_whatsapp ?? '' }}" placeholder="6281234567890" maxlength="20">
                            </div>
                            <div class="form-text">Nomor saja tanpa + atau 0, format: 62812xxxxxxx (akan otomatis jadi link wa.me)</div>
                        </div>
                        <div class="col-12 col-md-6">
                            <label for="contact_email" class="form-label fw-semibold">Email Kontak</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                                <input type="email" class="form-control" id="contact_email" name="contact_email" value="{{ $settings->contact_email ?? '' }}" placeholder="kontak@notaku.com" maxlength="255">
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <label for="contact_phone" class="form-label fw-semibold">Nomor Telepon</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-telephone"></i></span>
                                <input type="text" class="form-control" id="contact_phone" name="contact_phone" value="{{ $settings->contact_phone ?? '' }}" placeholder="021-12345678" maxlength="20">
                            </div>
                        </div>
                        <div class="col-12">
                            <label for="contact_address" class="form-label fw-semibold">Alamat</label>
                            <textarea class="form-control" id="contact_address" name="contact_address" rows="2" maxlength="500" placeholder="Jl. Contoh No. 123, Jakarta">{{ $settings->contact_address ?? '' }}</textarea>
                        </div>
                    </div>

                    <hr class="my-4">

                    {{-- Tombol Aksi --}}
                    <div class="d-flex gap-2 flex-wrap">
                        <x-button type="submit" variant="primary">
                            <i class="bi bi-save me-1"></i> Simpan Perubahan
                        </x-button>
                        <button type="button" class="btn btn-outline-secondary" id="btn-reset-defaults">
                            <i class="bi bi-arrow-counterclockwise me-1"></i> Reset ke Default
                        </button>
                    </div>
                </form>
            </div>
</div>

    {{-- Live Preview Panel --}}
    <div class="col-12 col-xl-4">
        <x-card class="sticky-top" style="top: 1.5rem;">
            <div class="card-header">
                <h2 class="card-title h5 mb-0">Live Preview</h2>
            </div>
            <div class="card-body">
                <div class="preview-section mb-4">
                    <h4 class="small text-muted text-uppercase mb-2">Navbar Brand</h4>
                    <div class="d-flex align-items-center gap-2 p-3 border rounded bg-light" id="preview-navbar">
                        <div class="preview-logo-wrapper" style="width: 36px; height: 36px;">
                            @if ($settings->logo_path)
                                <img src="{{ asset('storage/'.$settings->logo_path) }}" alt="" class="w-100 h-100 object-fit-cover rounded" id="preview-logo-img">
                            @else
                                <div class="w-100 h-100 rounded d-flex align-items-center justify-content-center bg-primary" id="preview-logo-fallback">
                                    <i class="bi bi-bag text-white"></i>
                                </div>
                            @endif
                        </div>
                        <span class="fw-semibold" id="preview-brand-name">{{ $settings->brand_name }}</span>
                    </div>
                </div>

                <div class="preview-section mb-4">
                    <h4 class="small text-muted text-uppercase mb-2">Buttons</h4>
                    <div class="d-flex flex-wrap gap-2" id="preview-buttons">
                        <button type="button" class="btn btn-sm" id="preview-btn-primary">Primary</button>
                        <button type="button" class="btn btn-sm" id="preview-btn-primary-dark" style="background-color: var(--color-primary-dark); border-color: var(--color-primary-dark);">Primary Dark</button>
                        <button type="button" class="btn btn-sm" id="preview-btn-secondary">Secondary</button>
                        <button type="button" class="btn btn-sm" id="preview-btn-secondary-dark" style="background-color: var(--color-secondary-dark); border-color: var(--color-secondary-dark);">Secondary Dark</button>
                        <button type="button" class="btn btn-success btn-sm" id="preview-btn-success">Success</button>
                        <button type="button" class="btn btn-warning btn-sm" id="preview-btn-warning">Warning</button>
                        <button type="button" class="btn btn-danger btn-sm" id="preview-btn-danger">Danger</button>
                    </div>
                </div>

                <div class="preview-section mb-4">
                    <h4 class="small text-muted text-uppercase mb-2">Badges</h4>
                    <div class="d-flex flex-wrap gap-2" id="preview-badges">
                        <span class="badge" id="preview-badge-primary">Primary</span>
                        <span class="badge" id="preview-badge-secondary">Secondary</span>
                        <span class="badge" id="preview-badge-success">Success</span>
                        <span class="badge" id="preview-badge-warning">Warning</span>
                        <span class="badge" id="preview-badge-danger">Danger</span>
                    </div>
                </div>

                <div class="preview-section mb-3">
                    <h4 class="small text-muted text-uppercase mb-2">Alerts</h4>
                    <div class="d-flex flex-column gap-2" id="preview-alerts">
                        <div class="alert alert-primary py-2 px-3 mb-0" id="preview-alert-primary">Primary alert example</div>
                        <div class="alert alert-secondary py-2 px-3 mb-0" id="preview-alert-secondary">Secondary alert example</div>
                        <div class="alert alert-success py-2 px-3 mb-0" id="preview-alert-success">Success alert example</div>
                        <div class="alert alert-warning py-2 px-3 mb-0" id="preview-alert-warning">Warning alert example</div>
                        <div class="alert alert-danger py-2 px-3 mb-0" id="preview-alert-danger">Danger alert example</div>
                    </div>
                </div>

                {{-- Creator Note --}}
                <div class="text-center pt-2 border-top">
                    <small class="text-muted">
                        Butuh update web ini sesuai request kamu? Hubungi creator
                        <a href="https://github.com/kink-jaki" target="_blank" class="text-primary text-decoration-none ms-1"><i class="bi bi-github"></i> kink-jaki</a>
                        <span class="text-danger ms-1"><i class="bi bi-instagram"></i> fzaky.13</span>
                    </small>
                </div>
            </div>
        </x-card>
    </div>
</div>
@endsection

@push('scripts')
<script>
    (function () {
        'use strict';

        // Preset color definitions (from PHP ThemePresets)
        const presets = @json(\App\Support\ThemePresets::all());

        const defaultValues = {
            brand_name: 'Notaku',
            color_primary: '#4F46E5',
            color_primary_dark: '#4338CA',
            color_secondary: '#64748B',
            color_secondary_dark: '#475569',
            color_success: '#10B981',
            color_warning: '#F59E0B',
            color_danger: '#EF4444',
        };

        const colorPickers = document.querySelectorAll('input[type="color"][data-preview]');
        const hexInputs = {
            'primary': document.getElementById('color_primary_hex'),
            'primary-dark': document.getElementById('color_primary_dark_hex'),
            'secondary': document.getElementById('color_secondary_hex'),
            'secondary-dark': document.getElementById('color_secondary_dark_hex'),
            'success': document.getElementById('color_success_hex'),
            'warning': document.getElementById('color_warning_hex'),
            'danger': document.getElementById('color_danger_hex'),
        };

        const themeVariantSelect = document.getElementById('theme_variant');
        const devModeCheckbox = document.getElementById('dev_mode');
        const colorPickerRows = document.querySelectorAll('[data-color-row]');

        // Apply preset colors to pickers
        function applyPreset(presetKey) {
            const preset = presets[presetKey];
            if (!preset) return;

            document.getElementById('color_primary').value = preset.color_primary;
            document.getElementById('color_primary_dark').value = preset.color_primary_dark;
            document.getElementById('color_secondary').value = preset.color_secondary;
            document.getElementById('color_secondary_dark').value = preset.color_secondary_dark;
            document.getElementById('color_success').value = preset.color_success;
            document.getElementById('color_warning').value = preset.color_warning;
            document.getElementById('color_danger').value = preset.color_danger;

            updatePreview();
        }

        // Toggle color picker visibility based on dev_mode
        function toggleColorPickers(show) {
            colorPickerRows.forEach(row => {
                row.style.display = show ? '' : 'none';
            });
        }

        // Update preview elements
        function updatePreview() {
            // Update navbar brand name
            const brandNameEl = document.getElementById('preview-brand-name');
            if (brandNameEl) {
                brandNameEl.textContent = document.getElementById('brand_name').value || defaultValues.brand_name;
            }

            // Update color pickers hex display
            colorPickers.forEach(picker => {
                const key = picker.dataset.preview;
                const hexInput = hexInputs[key];
                if (hexInput) {
                    hexInput.value = picker.value.toUpperCase();
                }
            });

            // Update CSS custom properties for preview elements only
            const root = document.documentElement;
            root.style.setProperty('--preview-primary', document.getElementById('color_primary').value);
            root.style.setProperty('--preview-primary-dark', document.getElementById('color_primary_dark').value);
            root.style.setProperty('--preview-secondary', document.getElementById('color_secondary').value);
            root.style.setProperty('--preview-secondary-dark', document.getElementById('color_secondary_dark').value);
            root.style.setProperty('--preview-success', document.getElementById('color_success').value);
            root.style.setProperty('--preview-warning', document.getElementById('color_warning').value);
            root.style.setProperty('--preview-danger', document.getElementById('color_danger').value);

            // Apply to preview elements
            applyPreviewStyles();
        }

        // Event: preset change
        if (themeVariantSelect) {
            themeVariantSelect.addEventListener('change', function () {
                // Only auto-apply preset if NOT in dev_mode
                if (!devModeCheckbox?.checked) {
                    applyPreset(this.value);
                }
            });
        }

        // Event: dev_mode toggle
        if (devModeCheckbox) {
            devModeCheckbox.addEventListener('change', function () {
                toggleColorPickers(this.checked);
                if (!this.checked) {
                    // When turning OFF dev_mode, re-apply current preset
                    applyPreset(themeVariantSelect?.value);
                }
            });
        }

        // Event listeners for color pickers
        colorPickers.forEach(picker => {
            picker.addEventListener('input', updatePreview);
        });

        // Brand name input listener
        document.getElementById('brand_name').addEventListener('input', updatePreview);

        // Logo preview update
        document.getElementById('logo').addEventListener('change', function (e) {
            const file = e.target.files[0];
            const previewImg = document.getElementById('logo-preview');
            const placeholder = document.getElementById('logo-preview-placeholder');
            const previewLogoImg = document.getElementById('preview-logo-img');
            const previewLogoFallback = document.getElementById('preview-logo-fallback');

            if (file) {
                const reader = new FileReader();
                reader.onload = function (event) {
                    if (previewImg) {
                        previewImg.src = event.target.result;
                        previewImg.style.display = 'block';
                    }
                    if (placeholder) {
                        placeholder.style.display = 'none';
                    }
                    if (previewLogoImg) {
                        previewLogoImg.src = event.target.result;
                        previewLogoImg.style.display = 'block';
                    }
                    if (previewLogoFallback) {
                        previewLogoFallback.style.display = 'none';
                    }
                };
                reader.readAsDataURL(file);
            }
        });

        // Favicon preview update
        document.getElementById('favicon').addEventListener('change', function (e) {
            const file = e.target.files[0];
            const previewImg = document.getElementById('favicon-preview');
            const placeholder = document.getElementById('favicon-preview-placeholder');

            if (file) {
                const reader = new FileReader();
                reader.onload = function (event) {
                    if (previewImg) {
                        previewImg.src = event.target.result;
                        previewImg.style.display = 'block';
                    }
                    if (placeholder) {
                        placeholder.style.display = 'none';
                    }
                };
                reader.readAsDataURL(file);
            }
        });

        // Reset to defaults
        document.getElementById('btn-reset-defaults').addEventListener('click', function () {
            if (!confirm('Yakin ingin mengembalikan semua pengaturan ke nilai default?')) {
                return;
            }

            document.getElementById('brand_name').value = defaultValues.brand_name;
            document.getElementById('color_primary').value = defaultValues.color_primary;
            document.getElementById('color_primary_dark').value = defaultValues.color_primary_dark;
            document.getElementById('color_secondary').value = defaultValues.color_secondary;
            document.getElementById('color_secondary_dark').value = defaultValues.color_secondary_dark;
            document.getElementById('color_success').value = defaultValues.color_success;
            document.getElementById('color_warning').value = defaultValues.color_warning;
            document.getElementById('color_danger').value = defaultValues.color_danger;
            document.getElementById('theme_variant').value = 'default';
            document.getElementById('dev_mode').checked = false;

            // Clear file inputs
            document.getElementById('logo').value = '';
            document.getElementById('favicon').value = '';

            // Reset logo preview
            const logoPreview = document.getElementById('logo-preview');
            const logoPlaceholder = document.getElementById('logo-preview-placeholder');
            const previewLogoImg = document.getElementById('preview-logo-img');
            const previewLogoFallback = document.getElementById('preview-logo-fallback');

            if (logoPreview && logoPlaceholder) {
                @if ($settings->logo_path)
                    logoPreview.src = "{{ asset('storage/'.$settings->logo_path) }}";
                    logoPreview.style.display = 'block';
                    logoPlaceholder.style.display = 'none';
                    if (previewLogoImg) {
                        previewLogoImg.src = "{{ asset('storage/'.$settings->logo_path) }}";
                        previewLogoImg.style.display = 'block';
                    }
                    if (previewLogoFallback) {
                        previewLogoFallback.style.display = 'none';
                    }
                @else
                    logoPreview.style.display = 'none';
                    logoPlaceholder.style.display = 'flex';
                    if (previewLogoImg) {
                        previewLogoImg.style.display = 'none';
                    }
                    if (previewLogoFallback) {
                        previewLogoFallback.style.display = 'flex';
                    }
                @endif
            }

            // Reset favicon preview
            const faviconPreview = document.getElementById('favicon-preview');
            const faviconPlaceholder = document.getElementById('favicon-preview-placeholder');

            if (faviconPreview && faviconPlaceholder) {
                @if ($settings->favicon_path)
                    faviconPreview.src = "{{ asset('storage/'.$settings->favicon_path) }}";
                    faviconPreview.style.display = 'block';
                    faviconPlaceholder.style.display = 'none';
                @else
                    faviconPreview.style.display = 'none';
                    faviconPlaceholder.style.display = 'flex';
                @endif
            }

            toggleColorPickers(false);
            updatePreview();
        });

        // Initialize on load
        document.addEventListener('DOMContentLoaded', function () {
            // Set initial color picker visibility
            toggleColorPickers(devModeCheckbox?.checked ?? false);
            updatePreview();
        });
    })();
</script>
@endpush