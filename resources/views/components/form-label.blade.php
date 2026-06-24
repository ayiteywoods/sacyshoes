@props([
    'for' => null,
    'required' => false,
])

<label
    @if ($for) for="{{ $for }}" @endif
    {{ $attributes->class('block text-sm font-medium') }}
>
    {{ $slot }}@if ($required)<span class="text-brand-red" aria-hidden="true"> *</span>@endif
</label>
