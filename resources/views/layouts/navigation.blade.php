@php
    $isCentral = !(function_exists('tenancy') && tenancy()->initialized);
    $adminEmails = array_filter(array_map('trim', explode(',', config('app.central_admin_emails', ''))));
    $isCentralAdmin = $isCentral && auth()->check() && in_array(auth()->user()->email, $adminEmails);
    $siteTitle = $isCentral ? \App\Models\Setting::get('saas_name', 'MovieShelf Cloud') : \App\Models\Setting::get('site_title', 'MovieShelf');
    $homeLink = Route::has('dashboard') ? route('dashboard') : (Route::has('landing') ? route('landing') : '/');
@endphp
<nav x-data="{ 
    open: false, 
    scrolled: window.pageYOffset > 20,
    layoutMode: '{{ optional(auth()->user())->layout ?? \App\Models\Setting::get("default_guest_layout", "classic") }}',
    activeMovieTitle: '',
    activeMovieCover: '',
    showMovieTitle: false,
}" 
x-init="window.addEventListener('scroll', () => { scrolled = window.pageYOffset > 20 })"
x-on:layout-change.window="if ($event.detail !== layoutMode) layoutMode = $event.detail"
x-on:set-active-movie.window="activeMovieTitle = $event.detail.title; activeMovieCover = $event.detail.cover"
x-on:toggle-movie-title.window="showMovieTitle = $event.detail.show"
class="z-50 px-8 py-6 transition-all duration-500 rounded-b-[2rem]"
:class="{
    'fixed top-0 left-0 right-0': layoutMode === 'streaming',
    'sticky top-0 bg-white/5': layoutMode !== 'streaming',
    'glass-dark border-b border-white/10 shadow-[0_20px_50px_-12px_rgba(0,0,0,0.5)] backdrop-blur-3xl': layoutMode !== 'streaming' || scrolled,
    'bg-transparent border-transparent shadow-none': layoutMode === 'streaming' && !scrolled
}">
    <div class="grid grid-cols-2 lg:grid-cols-3 items-center gap-4 text-white">
        <!-- Logo Section (Left) -->
        <div class="flex-shrink-0 flex items-center">
            <div class="relative h-12 overflow-hidden flex items-center px-2">
                <!-- Layer 1: Site Title -->
                <div class="transform transition-all duration-500 flex items-center gap-4"
                     :class="showMovieTitle ? '-translate-y-full opacity-0 scale-95' : 'translate-y-0 opacity-100 scale-100'">
                    <a href="{{ $homeLink }}" class="group flex items-center gap-4">
                        <x-application-logo class="h-10 w-auto group-hover:scale-110 transition-transform" />
                        <div>
                            <h2 class="text-xl font-black text-white uppercase tracking-tight leading-none group-hover:text-blue-400 transition-colors hidden sm:block">
                                {{ $siteTitle }}
                            </h2>
                            <h2 class="text-xl font-black text-white uppercase tracking-tight leading-none group-hover:text-blue-400 transition-colors sm:hidden">
                                MS
                            </h2>
                            <p class="text-[10px] text-gray-500 uppercase font-bold tracking-[0.2em] mt-1 italic hidden sm:block">
                                {{ $isCentral ? __('SaaS Platform') : __('Media Library') }}
                            </p>
                        </div>
                    </a>
                </div>

                <!-- Layer 2: Movie Title -->
                <div class="absolute inset-0 flex items-center transform transition-all duration-700 delay-75"
                     :class="showMovieTitle ? 'translate-y-0 opacity-100 scale-100' : 'translate-y-full opacity-0 scale-90'"
                     x-cloak>
                    <div class="flex items-center gap-4">
                        <div class="w-9 h-12 rounded-lg overflow-hidden border border-white/20 shadow-2xl flex-shrink-0 bg-white/5 backdrop-blur-md">
                            <template x-if="activeMovieCover">
                                <img :src="activeMovieCover" class="w-full h-full object-cover">
                            </template>
                            <template x-if="!activeMovieCover">
                                <div class="w-full h-full flex items-center justify-center">
                                    <i class="bi bi-film text-white/20 text-xs"></i>
                                </div>
                            </template>
                        </div>
                        <h2 class="text-xl md:text-2xl font-black text-white uppercase tracking-tight leading-none truncate max-w-[250px] md:max-w-[600px] lg:max-w-[800px] italic bg-clip-text text-transparent bg-gradient-to-r from-white to-white/60 pr-6">
                            <span x-text="activeMovieTitle"></span>
                        </h2>
                    </div>
                </div>
            </div>
        </div>

        <!-- Center Navigation (Desktop) -->
        <div class="hidden lg:flex items-center justify-center gap-2">
            @if(Route::has('dashboard'))
            <a href="{{ route('dashboard') }}"
                class="px-4 py-2 rounded-xl hover:bg-white/10 transition-colors flex items-center {{ request()->routeIs('dashboard') ? 'bg-white/10' : '' }}">
                <i class="bi bi-house-fill mr-2"></i> {{ __('Start') }}
            </a>
            @endif

            @if(Route::has('actors.index'))
            <a href="{{ route('actors.index') }}"
                class="px-4 py-2 rounded-xl hover:bg-white/10 transition-colors flex items-center {{ request()->routeIs('actors.index') ? 'bg-white/10' : '' }}">
                <i class="bi bi-people-fill mr-2"></i> {{ __('Actors') }}
            </a>
            @endif

            @if(Route::has('movies.trailers'))
            <a href="{{ route('movies.trailers') }}"
                class="px-4 py-2 rounded-xl hover:bg-white/10 transition-colors flex items-center {{ request()->routeIs('movies.trailers') ? 'bg-white/10' : '' }}">
                <i class="bi bi-play-circle mr-2"></i> {{ __('Trailers') }}
            </a>
            @endif

            @if(Route::has('dashboard'))
            <a href="{{ route('dashboard', ['stats' => 1]) }}"
                @click.prevent="if (window.location.pathname === '/' || window.location.pathname === '/dashboard') { $dispatch('stats-open') } else { window.location.href = $el.href }"
                class="px-4 py-2 rounded-xl hover:bg-white/10 transition-colors flex items-center {{ request()->routeIs('statistics') ? 'bg-white/10' : '' }}">
                <i class="bi bi-bar-chart-fill mr-2"></i> {{ __('Statistics') }}
            </a>
            @endif

            @auth
            @if(Route::has('lists.index'))
            <a href="{{ route('lists.index') }}"
                class="px-4 py-2 rounded-xl hover:bg-white/10 transition-colors flex items-center {{ request()->routeIs('lists.*') ? 'bg-white/10' : '' }}">
                <i class="bi bi-collection-fill mr-2"></i> {{ __('Lists') }}
            </a>
            @endif
            @endauth

            @if($isCentralAdmin && Route::has('cadmin.settings'))
            <a href="{{ route('cadmin.settings') }}"
                class="px-4 py-2 rounded-xl hover:bg-white/10 transition-colors flex items-center {{ request()->routeIs('cadmin.settings') ? 'bg-white/10' : '' }}">
                <i class="bi bi-gear-fill mr-2"></i> {{ __('SaaS Settings') }}
            </a>
            @endif
        </div>

        <!-- Search & User Section (Right) -->
        <div class="flex items-center gap-4 flex-1 justify-end">

            <div class="flex items-center gap-4">

                <!-- Language Switcher -->
                @if(Route::has('lang.switch'))
                <div class="flex items-center gap-2 px-2 border-r border-white/10 mr-2">
                    <a href="{{ route('lang.switch', 'de') }}"
                       class="text-[10px] font-black transition-all {{ app()->getLocale() == 'de' ? 'text-blue-400 scale-110' : 'text-gray-500 hover:text-white' }}"
                       title="Deutsch">
                        DE
                    </a>
                    <span class="text-white/10 text-[10px]">|</span>
                    <a href="{{ route('lang.switch', 'en') }}"
                       class="text-[10px] font-black transition-all {{ app()->getLocale() == 'en' ? 'text-blue-400 scale-110' : 'text-gray-500 hover:text-white' }}"
                       title="English">
                        EN
                    </a>
                </div>
                @endif

                <!-- Auth Section -->
                @auth
                    <!-- User Dropdown -->
                    @php
                        $isStreamingMode = (auth()->user()->layout ?? 'classic') === 'streaming';
                    @endphp
                    <x-dropdown align="right" width="48" :content-classes="$isStreamingMode ? 'p-2 bg-gray-950/80 backdrop-blur-3xl border border-white/10 rounded-2xl shadow-2xl' : 'py-1 bg-white'">
                        <x-slot name="trigger">
                            <button class="flex items-center gap-3 px-4 py-2 bg-white/5 hover:bg-white/10 border border-white/10 rounded-2xl transition-all active:scale-95 group">
                                <div class="h-8 w-8 rounded-xl bg-blue-500/20 flex items-center justify-center group-hover:scale-110 transition-transform">
                                    <i class="bi bi-person-fill text-blue-400"></i>
                                </div>
                                <span class="text-xs font-black uppercase tracking-widest hidden sm:inline">{{ Auth::user()->name }}</span>
                                <i class="bi bi-chevron-down text-[10px] text-gray-500 group-hover:text-white transition-colors"></i>
                            </button>
                        </x-slot>

                        <x-slot name="content">
                            <div class="space-y-1">
                                @if(Route::has('profile.edit'))
                                <x-dropdown-link :href="route('profile.edit')" class="rounded-xl flex items-center gap-3">
                                    <i class="bi bi-person-badge text-sm opacity-50"></i> 
                                    <span>{{ __('Profile') }}</span>
                                </x-dropdown-link>
                                @endif

                                @if($isCentralAdmin && Route::has('cadmin.dashboard'))
                                <x-dropdown-link :href="route('cadmin.dashboard')" class="rounded-xl flex items-center gap-3">
                                    <i class="bi bi-speedometer2 text-sm opacity-50"></i>
                                    <span>{{ __('Admin Panel') }}</span>
                                </x-dropdown-link>
                                @elseif(!$isCentral && auth()->user()?->is_admin && Route::has('admin.dashboard'))
                                <x-dropdown-link :href="route('admin.dashboard')" class="rounded-xl flex items-center gap-3">
                                    <i class="bi bi-speedometer2 text-sm opacity-50"></i>
                                    <span>{{ __('Admin Panel') }}</span>
                                </x-dropdown-link>
                                @endif

                                @if($isCentralAdmin && Route::has('cadmin.settings'))
                                <x-dropdown-link :href="route('cadmin.settings')" class="rounded-xl flex items-center gap-3">
                                    <i class="bi bi-gear-fill text-sm opacity-50"></i>
                                    <span>{{ __('SaaS Settings') }}</span>
                                </x-dropdown-link>
                                @endif

                                <div class="h-px bg-white/5 mx-2 my-1"></div>

                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <x-dropdown-link :href="route('logout')" class="rounded-xl text-red-500/70 hover:text-red-400 flex items-center gap-3"
                                            onclick="event.preventDefault(); this.closest('form').submit();">
                                        <i class="bi bi-box-arrow-right text-sm opacity-50"></i> 
                                        <span>{{ __('Log Out') }}</span>
                                    </x-dropdown-link>
                                </form>
                            </div>
                        </x-slot>
                    </x-dropdown>
                @else
                    <a href="{{ route('login') }}" class="flex items-center gap-2 px-6 py-2 bg-blue-600 hover:bg-blue-500 text-white rounded-xl transition-all font-bold text-sm shadow-lg shadow-blue-900/40">
                        <i class="bi bi-box-arrow-in-right"></i>
                        {{ __('Log in') }}
                    </a>
                @endauth

                <!-- Mobile Menu Toggle -->
                <button @click="open = ! open" class="lg:hidden p-2 hover:bg-white/10 rounded-xl transition-colors text-white">
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
        
        @if(Route::has('dashboard'))
        <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')" class="rounded-xl"> 
             {{ __('Start') }}
        </x-responsive-nav-link>
        @endif

        @if(Route::has('actors.index'))
        <a href="{{ route('actors.index') }}" class="block px-4 py-2 text-white hover:bg-white/10 rounded-xl transition-colors {{ request()->routeIs('actors.index') ? 'bg-white/10' : '' }}">
            <i class="bi bi-people-fill mr-2"></i> {{ __('Actors') }}
        </a>
        @endif

        @if(Route::has('movies.trailers'))
        <a href="{{ route('movies.trailers') }}" class="block px-4 py-2 text-white hover:bg-white/10 rounded-xl transition-colors {{ request()->routeIs('movies.trailers') ? 'bg-white/10' : '' }}">
            <i class="bi bi-play-circle mr-2"></i> {{ __('Trailers') }}
        </a>
        @endif

        @if(Route::has('dashboard'))
        <a href="{{ route('dashboard', ['stats' => 1]) }}"
            @click.prevent="if (window.location.pathname === '/' || window.location.pathname === '/dashboard') { $dispatch('stats-open'); open = false } else { window.location.href = $el.href }"
            class="block px-4 py-2 text-white hover:bg-white/10 rounded-xl transition-colors {{ request()->routeIs('statistics') ? 'bg-white/10' : '' }}">
            <i class="bi bi-bar-chart-fill mr-2"></i> {{ __('Statistics') }}
        </a>
        @endif

        <!-- Mobile Search -->
        @if(Route::has('dashboard'))
        <div class="pt-2 px-2">
            <form action="{{ $homeLink }}" method="GET" class="relative">
                <input type="text" name="q" value="{{ request('q') }}"
                    placeholder="{{ __('Search...') }}"
                    class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-2 pl-10 focus:ring-2 focus:ring-blue-500/50 text-sm transition-all text-white">
                <i class="bi bi-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-500"></i>
            </form>
        </div>
        @endif
    </div>
</nav>
