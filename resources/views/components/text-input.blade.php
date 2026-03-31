@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'w-full bg-white border border-gray-200 dark:bg-white/5 dark:border-white/10 rounded-xl px-4 py-2 focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500/50 text-gray-900 dark:text-white transition-all placeholder:text-gray-500']) }}>
