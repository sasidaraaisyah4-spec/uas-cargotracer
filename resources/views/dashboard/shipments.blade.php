@extends('layouts.dashboard')

@section('title', 'Shipments Management')
@section('header_title', 'All Shipments & Cargo Logs')

@section('content')
<div class="space-y-6">
    
    <!-- Top Action & Multi-filter Bar -->
    <div class="glass-panel p-5 rounded-2xl shadow-xl">
        <form action="{{ route('dashboard.shipments') }}" method="GET" class="grid grid-cols-1 md:grid-cols-12 gap-3 text-xs">
            <!-- Search -->
            <div class="md:col-span-4">
                <label class="block text-slate-400 font-semibold mb-1">Cari Kargo / Resi</label>
                <div class="relative">
                    <i class="fa-solid fa-magnifying-glass absolute left-3 top-2.5 text-slate-400"></i>
                    <input 
                        type="text" 
                        name="search" 
                        value="{{ request('search') }}" 
                        placeholder="No. Resi, Pengirim, Penerima..." 
                        class="w-full pl-9 pr-3 py-2 rounded-xl bg-slate-900/60 border border-white/10 text-white placeholder-slate-400 focus:outline-none focus:border-blue-500"
                    />
                </div>
            </div>

            <!-- Status Filter -->
            <div class="md:col-span-2">
                <label class="block text-slate-400 font-semibold mb-1">Status Kargo</label>
                <select name="status" class="w-full py-2 px-3 rounded-xl bg-slate-900/60 border border-white/10 text-white focus:outline-none focus:border-blue-500">
                    <option value="">Semua Status</option>
                    @foreach(['created', 'booked', 'picked_up', 'in_transit', 'out_for_delivery', 'exception', 'delivered', 'cancelled'] as $st)
                        <option value="{{ $st }}" {{ request('status') === $st ? 'selected' : '' }}>
                            {{ strtoupper(str_replace('_', ' ', $st)) }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Carrier Filter -->
            <div class="md:col-span-2">
                <label class="block text-slate-400 font-semibold mb-1">Carrier</label>
                <select name="carrier_id" class="w-full py-2 px-3 rounded-xl bg-slate-900/60 border border-white/10 text-white focus:outline-none focus:border-blue-500">
                    <option value="">Semua Carrier</option>
                    @foreach($carriers as $c)
                        <option value="{{ $c->id }}" {{ request('carrier_id') == $c->id ? 'selected' : '' }}>
                            {{ $c->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Delay Filter -->
            <div class="md:col-span-2">
                <label class="block text-slate-400 font-semibold mb-1">Kondisi Delay</label>
                <select name="is_delayed" class="w-full py-2 px-3 rounded-xl bg-slate-900/60 border border-white/10 text-white focus:outline-none focus:border-blue-500">
                    <option value="">Semua</option>
                    <option value="1" {{ request('is_delayed') === '1' ? 'selected' : '' }}>Hanya Delayed</option>
                    <option value="0" {{ request('is_delayed') === '0' ? 'selected' : '' }}>Hanya On-Time</option>
                </select>
            </div>

            <!-- Submit Button -->
            <div class="md:col-span-2 flex items-end">
                <button type="submit" class="w-full py-2 px-4 rounded-xl bg-blue-600 hover:bg-blue-500 text-white font-semibold transition flex items-center justify-center gap-1.5 shadow-lg shadow-blue-600/30">
                    <i class="fa-solid fa-filter"></i>
                    <span>Terapkan Filter</span>
                </button>
            </div>
        </form>
    </div>

    <!-- Shipments Table -->
    <div class="glass-panel p-6 rounded-2xl shadow-xl overflow-hidden">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-sm font-bold text-white flex items-center gap-2">
                <i class="fa-solid fa-boxes-packing text-blue-400"></i> Daftar Data Pengiriman
            </h3>
            @if(in_array(auth()->user()->role, ['admin', 'operator']))
                <a href="{{ route('dashboard.shipments.create') }}" class="px-3.5 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-semibold transition flex items-center gap-1.5 shadow-lg shadow-emerald-600/30">
                    <i class="fa-solid fa-plus"></i>
                    <span>Buat Pengiriman Baru</span>
                </a>
            @endif
        </div>

        <div class="overflow-x-auto custom-scrollbar">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="border-b border-white/10 text-slate-400 font-semibold">
                        <th class="py-3 px-4">Tracking Number</th>
                        <th class="py-3 px-4">Pengirim & Penerima</th>
                        <th class="py-3 px-4">Carrier & Servis</th>
                        <th class="py-3 px-4">Berat & Volume</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4">ETA & Delay</th>
                        <th class="py-3 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    @forelse($shipments as $s)
                        <tr class="hover:bg-white/[0.02] transition">
                            <td class="py-3.5 px-4 font-mono font-bold text-white">
                                <a href="{{ route('dashboard.shipments.show', $s->id) }}" class="text-cyan-400 hover:underline">
                                    {{ $s->tracking_number }}
                                </a>
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="font-semibold text-slate-200 block">{{ $s->sender_name }}</span>
                                <span class="text-slate-400 text-[11px]">&rarr; {{ $s->recipient_name }}</span>
                            </td>
                            <td class="py-3.5 px-4 text-slate-300">
                                <span class="block">{{ $s->carrier->name ?? 'N/A' }}</span>
                                <span class="text-slate-400 text-[11px] uppercase font-mono">{{ $s->service_type }}</span>
                            </td>
                            <td class="py-3.5 px-4 font-mono text-slate-300">
                                <span>{{ $s->total_weight }} kg</span>
                                <span class="block text-[11px] text-cyan-400">{{ $s->total_volume }} m&sup3;</span>
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider border {{ $s->status === 'delivered' ? 'bg-emerald-500/20 text-emerald-300 border-emerald-500/30' : ($s->status === 'exception' ? 'bg-rose-500/20 text-rose-300 border-rose-500/30' : 'bg-blue-500/20 text-blue-300 border-blue-500/30') }}">
                                    {{ str_replace('_', ' ', $s->status) }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4">
                                @if($s->is_delayed)
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-rose-500/20 text-rose-300 border border-rose-500/30">
                                        DELAY {{ $s->delay_hours }}h ({{ $s->delay_severity }})
                                    </span>
                                @else
                                    <span class="text-emerald-400 font-semibold text-[11px] flex items-center gap-1">
                                        <i class="fa-solid fa-check"></i> On-Time
                                    </span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-right">
                                <a href="{{ route('dashboard.shipments.show', $s->id) }}" class="px-3 py-1.5 rounded-lg bg-white/5 hover:bg-blue-600 text-slate-300 hover:text-white transition font-medium text-[11px] inline-flex items-center gap-1">
                                    <i class="fa-solid fa-eye"></i> Detail
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-8 text-slate-400">Tidak ada pengiriman yang cocok dengan filter.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination Links -->
        <div class="mt-4 pt-4 border-t border-white/5">
            {{ $shipments->appends(request()->query())->links() }}
        </div>
    </div>

</div>
@endsection

