@props(['value'])

<label for="{{ $attributes->get('for', '') }}" {{ $attributes->except('for')->merge(['class' => 'block font-bold text-xs uppercase tracking-widest text-gray-400 dark:text-white/50 mb-2']) }}>
    {{ $value ?? $slot }}
</label>
