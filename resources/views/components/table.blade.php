@props(['class' => ''])

<div class="table-wrap {{ $class }}">
    <table class="table table-hover align-middle">
        {{ $slot }}
    </table>
</div>
