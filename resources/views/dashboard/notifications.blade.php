@extends('layouts.dashboard')

@section('title', 'Notifikasi Saya')
@section('header_title', 'In-App Operational Notifications')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="glass-panel p-6 rounded-2xl shadow-xl">
        <div class="flex items-center justify-between mb-6 border-b border-white/10 pb-4">
            <div>
                <h3 class="text-sm font-bold text-white flex items-center gap-2">
                    <i class="fa-solid fa-bell text-amber-400"></i> Pemberitahuan Kargo
                </h3>
                <p class="text-xs text-slate-400">Notifikasi status, penundaan cuaca, dan tindak lanjut exception</p>
            </div>
            <span class="text-xs text-slate-400">{{ $notifications->total() }} Notifikasi</span>
        </div>

        <div class="space-y-3">
            @forelse($notifications as $n)
                <div class="p-4 rounded-2xl border transition flex items-start justify-between gap-4 {{ $n->is_read ? 'bg-slate-900/30 border-white/5 opacity-75' : 'bg-slate-900/80 border-blue-500/30 shadow-lg' }}">
                    <div class="flex items-start gap-3">
                        <div class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0 text-sm {{ $n->type === 'delay' || $n->type === 'exception' ? 'bg-rose-500/20 text-rose-400 border border-rose-500/30' : ($n->type === 'success' ? 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30' : 'bg-blue-500/20 text-blue-400 border border-blue-500/30') }}">
                            <i class="fa-solid {{ $n->type === 'delay' || $n->type === 'exception' ? 'fa-triangle-exclamation' : ($n->type === 'success' ? 'fa-check' : 'fa-info') }}"></i>
                        </div>
                        <div>
                            <span class="font-bold text-sm text-white block">{{ $n->title }}</span>
                            <p class="text-xs text-slate-300 mt-1 leading-relaxed">{{ $n->message }}</p>
                            <span class="text-[10px] text-slate-400 font-mono block mt-1.5">{{ $n->created_at->diffForHumans() }}</span>
                        </div>
                    </div>

                    @if(!$n->is_read)
                        <form action="{{ route('dashboard.notifications.read', $n->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="px-3 py-1 rounded-lg bg-white/5 hover:bg-white/10 text-slate-300 hover:text-white text-[11px] font-medium border border-white/5 transition whitespace-nowrap">
                                Tandai Dibaca
                            </button>
                        </form>
                    @endif
                </div>
            @empty
                <div class="text-center py-10 text-slate-400 text-xs">
                    Tidak ada notifikasi saat ini.
                </div>
            @endforelse
        </div>

        <div class="mt-4 pt-4 border-t border-white/5">
            {{ $notifications->links() }}
        </div>
    </div>
</div>
@endsection

