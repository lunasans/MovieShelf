@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'w-full bg-white/5 border border-white/10 rounded-xl px-4 py-2 focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500/50 text-white transition-all placeholder:text-gray-500']) }}>
