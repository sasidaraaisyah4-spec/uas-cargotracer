@extends('layouts.dashboard')

@section('title', 'Analytics Dashboard')
@section('header_title', 'Operational & Visibility Analytics')

@section('content')
<div class="space-y-8">
    
    <!-- Top KPI Cards Row -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-5">
        <!-- Total Shipments -->
        <div class="glass-panel p-5 rounded-2xl shadow-xl flex items-center justify-between">
            <div>
                <span class="text-xs text-slate-400 font-semibold uppercase tracking-wider block">Total Kargo</span>
                <span class="text-3xl font-black text-white font-mono mt-1 block">{{ $totalShipments }}</span>
            </div>
            <div class="w-12 h-12 rounded-xl bg-blue-500/10 text-blue-400 flex items-center justify-center text-xl border border-blue-500/20">
                <i class="fa-solid fa-boxes-stacked"></i>
            </div>
        </div>

        <!-- In Transit -->
        <div class="glass-panel p-5 rounded-2xl shadow-xl flex items-center justify-between">
            <div>
                <span class="text-xs text-cyan-400 font-semibold uppercase tracking-wider block">Dalam Perjalanan</span>
                <span class="text-3xl font-black text-cyan-400 font-mono mt-1 block">{{ $inTransitShipments }}</span>
            </div>
            <div class="w-12 h-12 rounded-xl bg-cyan-500/10 text-cyan-400 flex items-center justify-center text-xl border border-cyan-500/20">
                <i class="fa-solid fa-ship"></i>
            </div>
        </div>

        <!-- Delayed Shipments -->
        <div class="glass-panel p-5 rounded-2xl shadow-xl flex items-center justify-between">
            <div>
                <span class="text-xs text-rose-400 font-semibold uppercase tracking-wider block">Keterlambatan</span>
                <span class="text-3xl font-black text-rose-400 font-mono mt-1 block">{{ $delayedShipments }}</span>
            </div>
            <div class="w-12 h-12 rounded-xl bg-rose-500/10 text-rose-400 flex items-center justify-center text-xl border border-rose-500/20">
                <i class="fa-solid fa-clock-rotate-left"></i>
            </div>
        </div>

        <!-- Open Exceptions -->
        <div class="glass-panel p-5 rounded-2xl shadow-xl flex items-center justify-between">
            <div>
                <span class="text-xs text-amber-400 font-semibold uppercase tracking-wider block">Kendala Aktif</span>
                <span class="text-3xl font-black text-amber-400 font-mono mt-1 block">{{ $openExceptions }}</span>
            </div>
            <div class="w-12 h-12 rounded-xl bg-amber-500/10 text-amber-400 flex items-center justify-center text-xl border border-amber-500/20">
                <i class="fa-solid fa-triangle-exclamation"></i>
            </div>
        </div>

        <!-- Delivered Shipments -->
        <div class="glass-panel p-5 rounded-2xl shadow-xl flex items-center justify-between">
            <div>
                <span class="text-xs text-emerald-400 font-semibold uppercase tracking-wider block">Terkirim Sukses</span>
                <span class="text-3xl font-black text-emerald-400 font-mono mt-1 block">{{ $deliveredShipments }}</span>
            </div>
            <div class="w-12 h-12 rounded-xl bg-emerald-500/10 text-emerald-400 flex items-center justify-center text-xl border border-emerald-500/20">
                <i class="fa-solid fa-circle-check"></i>
            </div>
        </div>
    </div>

    <!-- Chart.js Visualizations Row -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <!-- Chart 1: Status Lifecycle Breakdown (Col 5) -->
        <div class="lg:col-span-5 glass-panel p-6 rounded-2xl shadow-xl">
            <h3 class="text-sm font-bold text-white mb-1 flex items-center gap-2">
                <i class="fa-solid fa-chart-pie text-blue-400"></i> Distribusi Status Pengiriman
            </h3>
            <p class="text-xs text-slate-400 mb-6">Metrik status kargo aktif berdasarkan database</p>
            <div class="relative h-64 flex items-center justify-center">
                <canvas id="statusChart"></canvas>
            </div>
        </div>

        <!-- Chart 2: Shipments by Carrier (Col 7) -->
        <div class="lg:col-span-7 glass-panel p-6 rounded-2xl shadow-xl">
            <h3 class="text-sm font-bold text-white mb-1 flex items-center gap-2">
                <i class="fa-solid fa-chart-column text-cyan-400"></i> Kargo per Mitra Ekspedisi (Carrier)
            </h3>
            <p class="text-xs text-slate-400 mb-6">Alokasi armada Maersk, DHL, Samudera Indonesia</p>
            <div class="relative h-64">
                <canvas id="carrierChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Recent Shipments Table -->
    <div class="glass-panel p-6 rounded-2xl shadow-xl">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h3 class="text-sm font-bold text-white flex items-center gap-2">
                    <i class="fa-solid fa-table-list text-blue-400"></i> Pengiriman Terbaru
                </h3>
                <p class="text-xs text-slate-400">Daftar muatan kargo terkini di platform</p>
            </div>
            <a href="{{ route('dashboard.shipments') }}" class="text-xs text-blue-400 hover:text-blue-300 font-semibold flex items-center gap-1.5">
                <span>Lihat Semua</span>
                <i class="fa-solid fa-arrow-right text-[10px]"></i>
            </a>
        </div>

        <div class="overflow-x-auto custom-scrollbar">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="border-b border-white/10 text-slate-400 font-semibold">
                        <th class="py-3 px-4">No. Resi</th>
                        <th class="py-3 px-4">Rute (Origin &rarr; Dest)</th>
                        <th class="py-3 px-4">Carrier</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4">Kondisi ETA</th>
                        <th class="py-3 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    @forelse($recentShipments as $s)
                        <tr class="hover:bg-white/[0.02] transition">
                            <td class="py-3.5 px-4 font-mono font-bold text-white">
                                {{ $s->tracking_number }}
                            </td>
                            <td class="py-3.5 px-4 text-slate-300">
                                {{ $s->originLocation->name ?? $s->sender_name }} 
                                <span class="text-slate-400 font-mono">&rarr;</span> 
                                {{ $s->destinationLocation->name ?? $s->recipient_name }}
                            </td>
                            <td class="py-3.5 px-4 text-slate-400">
                                {{ $s->carrier->name ?? 'N/A' }}
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider border {{ $s->status === 'delivered' ? 'bg-emerald-500/20 text-emerald-300 border-emerald-500/30' : ($s->status === 'exception' ? 'bg-rose-500/20 text-rose-300 border-rose-500/30' : 'bg-blue-500/20 text-blue-300 border-blue-500/30') }}">
                                    {{ str_replace('_', ' ', $s->status) }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4">
                                @if($s->is_delayed)
                                    <span class="text-rose-400 font-bold text-[11px] flex items-center gap-1">
                                        <i class="fa-solid fa-clock-rotate-left"></i> Delay {{ $s->delay_hours }}h ({{ $s->delay_severity }})
                                    </span>
                                @else
                                    <span class="text-emerald-400 font-semibold text-[11px] flex items-center gap-1">
                                        <i class="fa-solid fa-check"></i> On-Time
                                    </span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-right">
                                <a href="{{ route('dashboard.shipments.show', $s->id) }}" class="px-3 py-1 rounded-lg bg-white/5 hover:bg-blue-600 text-slate-300 hover:text-white transition font-medium text-[11px]">
                                    Detail
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-6 text-slate-400">Belum ada pengiriman.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // 1. Doughnut Chart: Status
        const ctxStatus = document.getElementById('statusChart').getContext('2d');
        new Chart(ctxStatus, {
            type: 'doughnut',
            data: {
                labels: @json(array_map(function($s){ return strtoupper(str_replace('_', ' ', $s)); }, $statuses)),
                datasets: [{
                    data: @json($statusCounts),
                    backgroundColor: [
                        '#64748b', // created
                        '#6366f1', // picked_up
                        '#3b82f6', // in_transit
                        '#a855f7', // out_for_delivery
                        '#ef4444', // exception
                        '#10b981', // delivered
                        '#f43f5e'  // cancelled
                    ],
                    borderColor: '#0f172a',
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { color: '#94a3b8', font: { family: 'Plus Jakarta Sans', size: 10 }, boxWidth: 12 }
                    }
                }
            }
        });

        // 2. Bar Chart: Carrier Allocation
        const ctxCarrier = document.getElementById('carrierChart').getContext('2d');
        new Chart(ctxCarrier, {
            type: 'bar',
            data: {
                labels: @json($carriers->pluck('name')),
                datasets: [{
                    label: 'Jumlah Kargo Aktif',
                    data: @json($carriers->pluck('shipments_count')),
                    backgroundColor: 'rgba(6, 182, 212, 0.7)',
                    borderColor: '#06b6d4',
                    borderWidth: 1.5,
                    borderRadius: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    x: { ticks: { color: '#94a3b8', font: { family: 'Plus Jakarta Sans', size: 10 } }, grid: { display: false } },
                    y: { ticks: { color: '#94a3b8', stepSize: 1, font: { family: 'Plus Jakarta Sans', size: 10 } }, grid: { color: 'rgba(255, 255, 255, 0.05)' } }
                },
                plugins: {
                    legend: { display: false }
                }
            }
        });
    });
</script>
@endsection

