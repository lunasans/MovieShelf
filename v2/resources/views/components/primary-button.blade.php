<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center px-6 py-2.5 bg-gradient-to-r from-blue-600 to-blue-500 hover:from-blue-500 hover:to-blue-400 text-white rounded-xl transition-all font-bold text-sm shadow-lg shadow-blue-900/40 border border-white/10 active:scale-95']) }}>
    {{ $slot }}
</button>
