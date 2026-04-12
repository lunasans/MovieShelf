@extends('cadmin.layout')

@section('title', 'E-Mail Templates - Global ACP')
@section('header_title', 'E-Mail Templates')

@section('content')
<div class="space-y-8">
    <div class="glass rounded-3xl overflow-hidden border border-white/5 shadow-2xl animate-in fade-in slide-in-from-bottom-4 duration-700">
        <div class="p-8 border-b border-white/5 flex justify-between items-center bg-white/[0.02]">
            <div>
                <h3 class="text-xl font-black text-white">System E-Mails</h3>
                <p class="text-gray-400 text-sm mt-1">Verwalte die automatisierten E-Mails deines MovieShelf SaaS.</p>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="text-[10px] font-black uppercase tracking-[0.2em] text-gray-500 border-b border-white/5">
                        <th class="px-8 py-5">Name & slug</th>
                        <th class="px-8 py-5">Betreff (Default)</th>
                        <th class="px-8 py-5">Letztes Update</th>
                        <th class="px-8 py-5 text-right">Aktion</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    @forelse($templates as $template)
                        <tr class="group hover:bg-white/[0.02] transition-all duration-300">
                            <td class="px-8 py-6">
                                <div class="font-bold text-white group-hover:text-rose-400 transition-colors">{{ $template->name }}</div>
                                <div class="text-[10px] font-mono text-gray-500 mt-1 opacity-60">{{ $template->slug }}</div>
                            </td>
                            <td class="px-8 py-6 text-sm text-gray-300 italic">
                                "{{ $template->subject }}"
                            </td>
                            <td class="px-8 py-6 text-xs text-gray-500">
                                {{ $template->updated_at->diffForHumans() }}
                            </td>
                            <td class="px-8 py-6 text-right">
                                <a href="{{ route('cadmin.email-templates.edit', $template) }}" 
                                   class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-white/5 hover:bg-rose-500/20 text-white hover:text-rose-400 transition-all border border-white/5">
                                    <i class="bi bi-pencil-square"></i>
                                    <span>Bearbeiten</span>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-8 py-12 text-center text-gray-500 italic">
                                Keine Templates gefunden.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
