<button {{ $attributes->merge(['type' => 'button', 'class' => 'inline-flex items-center px-6 py-2.5 bg-white/5 hover:bg-white/10 border border-white/10 rounded-xl font-bold text-sm text-white transition-all active:scale-95']) }}>
    {{ $slot }}
</button>
