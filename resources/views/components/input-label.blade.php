@props(['value', 'for' => null])

<label {{ $for ? 'for='.$for : '' }} {{ $attributes->merge(['class' => 'block font-bold text-xs uppercase tracking-widest text-gray-400 mb-2']) }}>
    {{ $value ?? $slot }}
</label>
