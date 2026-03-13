<nav x-data="{ open: false }" class="glass rounded-[20px] sticky top-6 z-50 px-6 py-5 transition-all duration-300 shadow-2xl">
    <div class="grid grid-cols-2 lg:grid-cols-3 items-center gap-4 text-white">
        <!-- Logo Section (Left) -->
        <div class="flex-shrink-0 flex items-center">
            <a href="{{ route('dashboard') }}" class="flex items-center gap-3 group">
                <x-application-logo class="h-10 w-auto drop-shadow-md group-hover:scale-105 transition-transform duration-500" />
                <span class="logo-text hidden sm:block text-lg font-bold tracking-tight">{{ \App\Models\Setting::get('site_title', 'MovieShelf') }}</span>
            </a>
        </div>

        <div class="hidden lg:flex items-center justify-center gap-2">
            <a href="{{ route('dashboard') }}" 
                class="px-4 py-2 rounded-xl hover:bg-white/10 transition-colors flex items-center {{ request()->routeIs('dashboard') ? 'bg-white/10' : '' }}">
                <i class="bi bi-house-fill mr-2"></i> {{ __('Start') }}
            </a>
            <a href="{{ route('actors.index') }}" 
                class="px-4 py-2 rounded-xl hover:bg-white/10 transition-colors flex items-center {{ request()->routeIs('actors.index') ? 'bg-white/10' : '' }}">
                <i class="bi bi-people-fill mr-2"></i> {{ __('Schauspieler') }}
            </a>
            <a href="{{ route('movies.trailers') }}" 
                class="px-4 py-2 rounded-xl hover:bg-white/10 transition-colors flex items-center {{ request()->routeIs('movies.trailers') ? 'bg-white/10' : '' }}">
                <i class="bi bi-play-circle mr-2"></i> {{ __('Trailer') }}
            </a>
            <a href="{{ route('dashboard', ['stats' => 1]) }}" 
                @click.prevent="if (window.location.pathname === '/' || window.location.pathname === '/dashboard') { $dispatch('stats-open') } else { window.location.href = $el.href }"
                class="px-4 py-2 rounded-xl hover:bg-white/10 transition-colors flex items-center {{ request()->routeIs('statistics') ? 'bg-white/10' : '' }}">
                <i class="bi bi-bar-chart-fill mr-2"></i> {{ __('Statistik') }}
            </a>
        </div>

        <!-- Search & User Section (Right) -->
        <div class="flex items-center gap-4 flex-1 justify-end">
            <!-- Search Form -->
            <form action="{{ route('dashboard') }}" method="GET" class="relative hidden xl:block w-full max-w-[200px]">
                <input type="text" name="q" value="{{ request('q') }}" 
                    placeholder="{{ __('Search...') }}" 
                    class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-2 pl-10 focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500/50 text-sm transition-all placeholder:text-gray-500">
                <i class="bi bi-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-500"></i>
            </form>

            <div class="flex items-center gap-4">
                <!-- Auth Section -->
                @auth
                    <!-- User Dropdown -->
                    <x-dropdown align="right" width="48">
                        <x-slot name="trigger">
                            <button class="flex items-center gap-2 px-3 py-2 bg-white/5 hover:bg-white/10 border border-white/10 rounded-xl transition-colors">
                                <div class="h-8 w-8 rounded-lg bg-blue-500/20 flex items-center justify-center">
                                    <i class="bi bi-person-fill text-blue-400"></i>
                                </div>
                                <span class="text-sm font-medium hidden sm:inline">{{ Auth::user()->name }}</span>
                                <i class="bi bi-chevron-down text-xs text-gray-400"></i>
                            </button>
                        </x-slot>

                        <x-slot name="content">
                            <div class="p-2 space-y-1">
                                <x-dropdown-link :href="route('profile.edit')" class="rounded-lg">
                                    <i class="bi bi-person mr-2"></i> {{ __('Profile') }}
                                </x-dropdown-link>

                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <x-dropdown-link :href="route('logout')" class="rounded-lg text-red-400 hover:text-red-300"
                                            onclick="event.preventDefault(); this.closest('form').submit();">
                                        <i class="bi bi-box-arrow-right mr-2"></i> {{ __('Log Out') }}
                                    </x-dropdown-link>
                                </form>
                            </div>
                        </x-slot>
                    </x-dropdown>
                @else
                    <a href="{{ route('login') }}" class="flex items-center gap-2 px-6 py-2 bg-blue-600 hover:bg-blue-500 text-white rounded-xl transition-all font-bold text-sm shadow-lg shadow-blue-900/40">
                        <i class="bi bi-box-arrow-in-right"></i>
                        {{ __('Login') }}
                    </a>
                @endauth

                <!-- Mobile Menu Toggle -->
                <button @click="open = ! open" class="lg:hidden p-2 hover:bg-white/10 rounded-xl transition-colors">
                    <i class="bi" :class="open ? 'bi-x-lg' : 'bi-list'"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- Mobile Menu -->
    <div x-show="open" 
         x-cloak
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 -translate-y-4"
         x-transition:enter-end="opacity-100 translate-y-0"
         class="lg:hidden mt-4 pt-4 border-t border-white/10 space-y-2">
        <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')" class="rounded-xl">
             {{ __('Start') }}
        </x-responsive-nav-link>
        <a href="{{ route('actors.index') }}" class="block px-4 py-2 text-white hover:bg-white/10 rounded-xl transition-colors {{ request()->routeIs('actors.index') ? 'bg-white/10' : '' }}">
            {{ __('Schauspieler') }}
        </a>
        <a href="#" class="block px-4 py-2 text-white hover:bg-white/10 rounded-xl transition-colors">
            {{ __('Trailer') }}
        </a>
        <a href="{{ route('dashboard', ['stats' => 1]) }}" 
            @click.prevent="if (window.location.pathname === '/' || window.location.pathname === '/dashboard') { $dispatch('stats-open'); open = false } else { window.location.href = $el.href }"
            class="block px-4 py-2 text-white hover:bg-white/10 rounded-xl transition-colors {{ request()->routeIs('statistics') ? 'bg-white/10' : '' }}">
            {{ __('Statistik') }}
        </a>
        
        <!-- Mobile Search -->
        <div class="pt-2 px-2">
            <form action="{{ route('dashboard') }}" method="GET" class="relative">
                <input type="text" name="q" value="{{ request('q') }}" 
                    placeholder="{{ __('Search...') }}" 
                    class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-2 pl-10 focus:ring-2 focus:ring-blue-500/50 text-sm transition-all">
                <i class="bi bi-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-500"></i>
            </form>
        </div>
    </div>
</nav>
