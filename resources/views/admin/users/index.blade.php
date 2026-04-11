<x-admin-layout>
    <div class="p-6 md:p-10" x-data="{
        showAddModal: {{ $errors->any() && !session('editing_user_id') ? 'true' : 'false' }},
        showEditModal: {{ session('editing_user_id') ? 'true' : 'false' }},
        editingUser: {!! session('editing_user_id') ? (\App\Models\User::find(session('editing_user_id'))?->toJson() ?? 'null') : 'null' !!},
        openEditModal(user) {
            this.editingUser = user;
            this.showEditModal = true;
        }
    }">
        <div class="mb-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div>
                <h1 class="text-4xl font-black text-white tracking-tight">Benutzer</h1>
                <p class="text-white/40 mt-1 uppercase text-xs font-black tracking-[0.3em]">Systemzugänge & Berechtigungen</p>
            </div>
            <button @click="showAddModal = true" class="px-8 py-4 bg-rose-600 hover:bg-rose-500 text-white rounded-2xl font-black text-xs uppercase tracking-widest transition-all shadow-xl shadow-rose-500/20 flex items-center gap-3 group">
                <i class="bi bi-person-plus-fill text-lg group-hover:scale-125 transition-transform"></i>
                Neuer Benutzer
            </button>
        </div>

        <div class="glass overflow-hidden rounded-[3rem] border-white/5 shadow-2xl">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-white/[0.02] border-b border-white/5">
                            <th class="px-10 py-6 text-[10px] font-black text-white/30 uppercase tracking-[0.3em]">Benutzer</th>
                            <th class="px-8 py-6 text-[10px] font-black text-white/30 uppercase tracking-[0.3em]">Sicherheit</th>
                            <th class="px-8 py-6 text-[10px] font-black text-white/30 uppercase tracking-[0.3em]">Aktivität</th>
                            <th class="px-8 py-6 text-[10px] font-black text-white/30 uppercase tracking-[0.3em] text-right">Aktionen</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5">
                        @foreach($users as $user)
                            <tr class="group hover:bg-white/[0.03] transition-colors">
                                <td class="px-10 py-6">
                                    <div class="flex items-center gap-5">
                                        <div class="w-14 h-14 bg-gradient-to-br from-rose-600 to-red-800 rounded-2xl flex items-center justify-center text-white font-black text-xl shadow-lg ring-2 ring-white/10 group-hover:scale-105 transition-transform">
                                            {{ substr($user->name, 0, 1) }}
                                        </div>
                                        <div>
                                            <div class="text-white font-black text-base group-hover:text-rose-400 transition-colors">{{ $user->name }}</div>
                                            <div class="text-white/30 text-[11px] font-medium tracking-wide">{{ $user->email }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-8 py-6">
                                    @if($user->two_factor_confirmed_at)
                                        <span class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 text-[10px] font-black uppercase tracking-widest">
                                            <i class="bi bi-shield-check"></i> 2FA Aktiv
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-white/5 text-white/20 border border-white/10 text-[10px] font-black uppercase tracking-widest">
                                            <i class="bi bi-shield-slash"></i> Inaktiv
                                        </span>
                                    @endif
                                </td>
                                <td class="px-8 py-6">
                                    <div class="flex items-center gap-2 text-white/60 text-xs font-black uppercase tracking-widest">
                                        <i class="bi bi-film text-rose-500"></i> {{ $user->watched_movies_count }} Filme
                                    </div>
                                    <div class="text-white/20 text-[10px] mt-1.5 uppercase tracking-widest font-bold italic">
                                        Seit {{ $user->created_at->format('d.m.Y') }}
                                    </div>
                                </td>
                                <td class="px-8 py-6 text-right">
                                    <div class="flex justify-end gap-3 opacity-20 group-hover:opacity-100 transition-opacity">
                                        <button @click="openEditModal({{ $user->toJson() }})" class="w-10 h-10 rounded-xl bg-white/5 flex items-center justify-center text-white/40 hover:bg-rose-500 hover:text-white transition-all" title="Bearbeiten">
                                            <i class="bi bi-pencil-square"></i>
                                        </button>
                                        @if($user->id !== auth()->id())
                                            <form action="{{ route('admin.users.destroy', $user) }}" method="POST" onsubmit="return confirm('Benutzer wirklich löschen?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="w-10 h-10 rounded-xl bg-white/5 flex items-center justify-center text-rose-500/40 hover:bg-rose-500 hover:text-white transition-all shadow-rose-500/30" title="Löschen">
                                                    <i class="bi bi-trash3-fill"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Add User Modal -->
        <template x-teleport="body">
            <div x-show="showAddModal" x-cloak class="fixed inset-0 z-[9999] flex items-center justify-center p-6"
                 x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
                <div class="absolute inset-0 bg-black/80 backdrop-blur-md" @click="showAddModal = false"></div>
                <div class="glass relative w-full max-w-md p-10 rounded-[3rem] border-white/10 shadow-3xl"
                     x-show="showAddModal"
                     x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100">
                    <h2 class="text-3xl font-black text-white mb-8 tracking-tight">Neuer Benutzer</h2>
                    <form action="{{ route('admin.users.store') }}" method="POST" class="space-y-6">
                        @csrf
                        <div>
                            <label class="block text-[10px] font-black text-white/30 uppercase tracking-[0.2em] mb-3 px-1">Name</label>
                            <input type="text" name="name" value="{{ old('name') }}" required class="w-full bg-white/5 border border-white/10 rounded-2xl px-6 py-4 text-white placeholder:text-white/20 focus:outline-none focus:border-rose-500/50 focus:ring-4 focus:ring-rose-500/10 transition-all @error('name') border-rose-500/50 ring-4 ring-rose-500/10 @enderror">
                            @error('name') <p class="text-rose-400 text-[10px] mt-2 ml-1 font-bold">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-white/30 uppercase tracking-[0.2em] mb-3 px-1">E-Mail</label>
                            <input type="email" name="email" value="{{ old('email') }}" required class="w-full bg-white/5 border border-white/10 rounded-2xl px-6 py-4 text-white placeholder:text-white/20 focus:outline-none focus:border-rose-500/50 focus:ring-4 focus:ring-rose-500/10 transition-all @error('email') border-rose-500/50 ring-4 ring-rose-500/10 @enderror">
                            @error('email') <p class="text-rose-400 text-[10px] mt-2 ml-1 font-bold">{{ $message }}</p> @enderror
                        </div>
                        <div class="grid grid-cols-1 gap-6">
                            <div>
                                <label class="block text-[10px] font-black text-white/30 uppercase tracking-[0.2em] mb-3 px-1">Passwort</label>
                                <input type="password" name="password" required class="w-full bg-white/5 border border-white/10 rounded-2xl px-6 py-4 text-white placeholder:text-white/20 focus:outline-none focus:border-rose-500/50 focus:ring-4 focus:ring-rose-500/10 transition-all @error('password') border-rose-500/50 ring-4 ring-rose-500/10 @enderror">
                                @error('password') <p class="text-rose-400 text-[10px] mt-2 ml-1 font-bold">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-[10px] font-black text-white/30 uppercase tracking-[0.2em] mb-3 px-1">Bestätigen</label>
                                <input type="password" name="password_confirmation" required class="w-full bg-white/5 border border-white/10 rounded-2xl px-6 py-4 text-white focus:outline-none focus:border-rose-500/50 focus:ring-4 focus:ring-rose-500/10 transition-all">
                            </div>
                        </div>
                        <div class="flex flex-col gap-4 pt-6">
                            <button type="submit" class="w-full py-5 bg-rose-600 text-white font-black rounded-2xl hover:bg-rose-500 transition-all shadow-xl shadow-rose-500/20 uppercase tracking-widest text-xs">Benutzer erstellen</button>
                            <button type="button" @click="showAddModal = false" class="w-full py-4 bg-white/5 text-white/30 font-black rounded-2xl hover:bg-white/10 transition-all uppercase tracking-widest text-xs">Abbrechen</button>
                        </div>
                    </form>
                </div>
            </div>
        </template>

        <!-- Edit User Modal -->
        <template x-teleport="body">
            <div x-show="showEditModal" x-cloak class="fixed inset-0 z-[9999] flex items-center justify-center p-6"
                 x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
                <div class="absolute inset-0 bg-black/80 backdrop-blur-md" @click="showEditModal = false"></div>
                <div class="glass relative w-full max-w-md p-10 rounded-[3rem] border-white/10 shadow-3xl"
                     x-show="showEditModal"
                     x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100">
                    <h2 class="text-3xl font-black text-white mb-8 tracking-tight">Profil bearbeiten</h2>
                    <form :action="'{{ url('admin/users') }}/' + editingUser.id" method="POST" class="space-y-6">
                        @csrf
                        @method('PATCH')
                        <div>
                            <label class="block text-[10px] font-black text-white/30 uppercase tracking-[0.2em] mb-3 px-1">Anzeigename</label>
                            <input type="text" name="name" :value="editingUser.name" required class="w-full bg-white/5 border border-white/10 rounded-2xl px-6 py-4 text-white focus:outline-none focus:border-rose-500/50 focus:ring-4 focus:ring-rose-500/10 transition-all @error('name') border-rose-500/50 ring-4 ring-rose-500/10 @enderror">
                            @error('name') <p class="text-rose-400 text-[10px] mt-2 ml-1 font-bold">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-white/30 uppercase tracking-[0.2em] mb-3 px-1">E-Mail Adresse</label>
                            <input type="email" name="email" :value="editingUser.email" required class="w-full bg-white/5 border border-white/10 rounded-2xl px-6 py-4 text-white focus:outline-none focus:border-rose-500/50 focus:ring-4 focus:ring-rose-500/10 transition-all @error('email') border-rose-500/50 ring-4 ring-rose-500/10 @enderror">
                            @error('email') <p class="text-rose-400 text-[10px] mt-2 ml-1 font-bold">{{ $message }}</p> @enderror
                        </div>
                        <div class="grid grid-cols-1 gap-6">
                            <div>
                                <label class="block text-[10px] font-black text-white/30 uppercase tracking-[0.2em] mb-3 px-1">Neues Passwort (optional)</label>
                                <input type="password" name="password" class="w-full bg-white/5 border border-white/10 rounded-2xl px-6 py-4 text-white focus:outline-none focus:border-rose-500/50 focus:ring-4 focus:ring-rose-500/10 transition-all @error('password') border-rose-500/50 ring-4 ring-rose-500/10 @enderror" placeholder="••••••••">
                                @error('password') <p class="text-rose-400 text-[10px] mt-2 ml-1 font-bold">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-[10px] font-black text-white/30 uppercase tracking-[0.2em] mb-3 px-1">Bestätigen</label>
                                <input type="password" name="password_confirmation" class="w-full bg-white/5 border border-white/10 rounded-2xl px-6 py-4 text-white focus:outline-none focus:border-rose-500/50 focus:ring-4 focus:ring-rose-500/10 transition-all">
                            </div>
                        </div>
                        <div class="flex flex-col gap-4 pt-6">
                            <button type="submit" class="w-full py-5 bg-rose-600 text-white font-black rounded-2xl hover:bg-rose-500 transition-all shadow-xl shadow-rose-500/20 uppercase tracking-widest text-xs">Änderungen speichern</button>
                            <button type="button" @click="showEditModal = false" class="w-full py-4 bg-white/5 text-white/30 font-black rounded-2xl hover:bg-white/10 transition-all uppercase tracking-widest text-xs">Abbrechen</button>
                        </div>
                    </form>
                </div>
            </div>
        </template>
    </div>
</x-admin-layout>