@props(['show' => false, 'size' => 'modal-lg', 'title' => ''])

<div class="modal fade {{ $show ? 'show d-block' : '' }}" tabindex="-1" style="{{ $show ? 'display: block; opacity: 1;' : '' }}">
    <div class="modal-dialog {{ $size === 'modal-lg' ? 'modal-lg' : ($size === 'modal-sm' ? 'modal-sm' : '') }}">
        <div class="modal-content">
            @if ($title)
                <div class="modal-header">
                    <h5 class="modal-title">{{ $title }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
            @endif
            <div class="modal-body">
                {{ $slot }}
            </div>
            @if (! empty($hasFooter) ?? false)
                <div class="modal-footer">
                    {{ $footer }}
                </div>
            @endif
        </div>
    </div>
</div>
@if ($show)
    <div class="modal-backdrop fade show"></div>
@endif
