<x-admin-layout>
    <div class="p-6 md:p-10" x-data="{ 
        showAddModal: false, 
        showEditModal: false,
        editingUser: null,
        openEditModal(user) {
            this.editingUser = user;
            this.showEditModal = true;
        }
    }">
        <div class="mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h1 class="text-3xl font-black text-white tracking-tight">Benutzer</h1>
                <p class="text-white/50 mt-1 uppercase text-xs font-bold tracking-widest">Systemzugänge verwalten</p>
            </div>
            <button @click="showAddModal = true" class="glass-button flex items-center gap-2 group bg-blue-500/20 text-blue-400 border-blue-500/30">
                <i class="bi bi-person-plus text-lg"></i>
                Neuer Benutzer
            </button>
        </div>

        <div class="glass overflow-hidden rounded-[2.5rem] border-white/5">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-white/5">
                            <th class="px-8 py-5 text-[10px] font-black text-white/40 uppercase tracking-[0.2em]">Benutzer</th>
                            <th class="px-8 py-5 text-[10px] font-black text-white/40 uppercase tracking-[0.2em]">Sicherheit</th>
                            <th class="px-8 py-5 text-[10px] font-black text-white/40 uppercase tracking-[0.2em]">Aktivität</th>
                            <th class="px-8 py-5 text-[10px] font-black text-white/40 uppercase tracking-[0.2em] text-right">Aktionen</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5">
                        @foreach($users as $user)
                            <tr class="group hover:bg-white/[0.02] transition-colors">
                                <td class="px-8 py-6">
                                    <div class="flex items-center gap-4">
                                        <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-purple-600 rounded-2xl flex items-center justify-center text-white font-black text-xl shadow-lg">
                                            {{ substr($user->name, 0, 1) }}
                                        </div>
                                        <div>
                                            <div class="text-white font-bold">{{ $user->name }}</div>
                                            <div class="text-white/30 text-xs">{{ $user->email }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-8 py-6">
                                    @if($user->two_factor_confirmed_at)
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 text-[10px] font-black uppercase tracking-widest">
                                            <i class="bi bi-shield-check"></i> 2FA Aktiv
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-white/5 text-white/30 border border-white/10 text-[10px] font-black uppercase tracking-widest">
                                            <i class="bi bi-shield-slash"></i> Inaktiv
                                        </span>
                                    @endif
                                </td>
                                <td class="px-8 py-6">
                                    <div class="text-white/60 text-xs font-bold">
                                        <i class="bi bi-film mr-1 text-blue-400"></i> {{ $user->watched_movies_count }} Filme gesehen
                                    </div>
                                    <div class="text-white/20 text-[10px] mt-1 uppercase tracking-widest font-black">
                                        Dabei seit {{ $user->created_at->format('d.m.Y') }}
                                    </div>
                                </td>
                                <td class="px-8 py-6 text-right">
                                    <div class="flex justify-end gap-2">
                                        <button @click="openEditModal({{ $user }})" class="p-2 text-white/20 hover:text-white transition-colors" title="Bearbeiten">
                                            <i class="bi bi-pencil-square"></i>
                                        </button>
                                        @if($user->id !== auth()->id())
                                            <form action="{{ route('admin.users.destroy', $user) }}" method="POST" onsubmit="return confirm('Benutzer wirklich löschen?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="p-2 text-white/20 hover:text-rose-500 transition-colors" title="Löschen">
                                                    <i class="bi bi-trash3"></i>
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
        <template x-if="showAddModal">
            <div class="fixed inset-0 z-50 flex items-center justify-center p-6">
                <div class="absolute inset-0 bg-black/80 backdrop-blur-sm" @click="showAddModal = false"></div>
                <div class="glass relative w-full max-w-md p-8 rounded-[2.5rem] border-white/10 shadow-2xl">
                    <h2 class="text-2xl font-black text-white mb-6">Neuer Benutzer</h2>
                    <form action="{{ route('admin.users.store') }}" method="POST" class="space-y-4">
                        @csrf
                        <div>
                            <label class="block text-[10px] font-black text-white/40 uppercase tracking-widest mb-2 px-1">Name</label>
                            <input type="text" name="name" required class="w-full bg-white/5 border border-white/10 rounded-2xl px-5 py-3 text-white focus:outline-none focus:ring-2 focus:ring-blue-500/50 transition-all">
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-white/40 uppercase tracking-widest mb-2 px-1">E-Mail</label>
                            <input type="email" name="email" required class="w-full bg-white/5 border border-white/10 rounded-2xl px-5 py-3 text-white focus:outline-none focus:ring-2 focus:ring-blue-500/50 transition-all">
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-white/40 uppercase tracking-widest mb-2 px-1">Passwort</label>
                            <input type="password" name="password" required class="w-full bg-white/5 border border-white/10 rounded-2xl px-5 py-3 text-white focus:outline-none focus:ring-2 focus:ring-blue-500/50 transition-all">
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-white/40 uppercase tracking-widest mb-2 px-1">Bestätigen</label>
                            <input type="password" name="password_confirmation" required class="w-full bg-white/5 border border-white/10 rounded-2xl px-5 py-3 text-white focus:outline-none focus:ring-2 focus:ring-blue-500/50 transition-all">
                        </div>
                        <div class="flex gap-3 pt-4">
                            <button type="submit" class="flex-1 py-4 bg-blue-500 text-white font-black rounded-2xl hover:bg-blue-600 transition-colors uppercase tracking-widest text-xs">Erstellen</button>
                            <button type="button" @click="showAddModal = false" class="px-6 py-4 bg-white/5 text-white/40 font-black rounded-2xl hover:bg-white/10 transition-colors uppercase tracking-widest text-xs">Abbrechen</button>
                        </div>
                    </form>
                </div>
            </div>
        </template>

        <!-- Edit User Modal -->
        <template x-if="showEditModal">
            <div class="fixed inset-0 z-50 flex items-center justify-center p-6">
                <div class="absolute inset-0 bg-black/80 backdrop-blur-sm" @click="showEditModal = false"></div>
                <div class="glass relative w-full max-w-md p-8 rounded-[2.5rem] border-white/10 shadow-2xl">
                    <h2 class="text-2xl font-black text-white mb-6">Benutzer bearbeiten</h2>
                    <form :action="'{{ url('admin/users') }}/' + editingUser.id" method="POST" class="space-y-4">
                        @csrf
                        @method('PATCH')
                        <div>
                            <label class="block text-[10px] font-black text-white/40 uppercase tracking-widest mb-2 px-1">Name</label>
                            <input type="text" name="name" :value="editingUser.name" required class="w-full bg-white/5 border border-white/10 rounded-2xl px-5 py-3 text-white focus:outline-none focus:ring-2 focus:ring-blue-500/50 transition-all">
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-white/40 uppercase tracking-widest mb-2 px-1">E-Mail</label>
                            <input type="email" name="email" :value="editingUser.email" required class="w-full bg-white/5 border border-white/10 rounded-2xl px-5 py-3 text-white focus:outline-none focus:ring-2 focus:ring-blue-500/50 transition-all">
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-white/40 uppercase tracking-widest mb-2 px-1">Neues Passwort (optional)</label>
                            <input type="password" name="password" class="w-full bg-white/5 border border-white/10 rounded-2xl px-5 py-3 text-white focus:outline-none focus:ring-2 focus:ring-blue-500/50 transition-all" placeholder="Leer lassen zum Beibehalten">
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-white/40 uppercase tracking-widest mb-2 px-1">Bestätigen</label>
                            <input type="password" name="password_confirmation" class="w-full bg-white/5 border border-white/10 rounded-2xl px-5 py-3 text-white focus:outline-none focus:ring-2 focus:ring-blue-500/50 transition-all">
                        </div>
                        <div class="flex gap-3 pt-4">
                            <button type="submit" class="flex-1 py-4 bg-purple-500 text-white font-black rounded-2xl hover:bg-purple-600 transition-colors uppercase tracking-widest text-xs">Speichern</button>
                            <button type="button" @click="showEditModal = false" class="px-6 py-4 bg-white/5 text-white/40 font-black rounded-2xl hover:bg-white/10 transition-colors uppercase tracking-widest text-xs">Abbrechen</button>
                        </div>
                    </form>
                </div>
            </div>
        </template>
    </div>

    <style>
        .glass-button {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 1rem;
            font-size: 0.875rem;
            font-weight: 700;
            transition: all 0.3s ease;
        }
        .glass-button:hover {
            background: rgba(255, 255, 255, 0.1);
            border-color: rgba(255, 255, 255, 0.2);
            transform: translateY(-1px);
        }
    </style>
</x-admin-layout>
