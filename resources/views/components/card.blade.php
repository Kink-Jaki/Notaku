@props(['class' => '', 'hover' => true])

<div class="card {{ $class }}" {{ $hover ? '' : '' }}>
    {{ $slot }}
</div>
