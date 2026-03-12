<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center px-6 py-2.5 bg-rose-600/20 hover:bg-rose-600/30 border border-rose-500/30 text-rose-400 rounded-xl transition-all font-bold text-sm active:scale-95']) }}>
    {{ $slot }}
</button>
