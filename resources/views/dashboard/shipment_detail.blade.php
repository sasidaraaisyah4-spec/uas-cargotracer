@extends('layouts.dashboard')

@section('title', 'Shipment ' . $shipment->tracking_number)
@section('header_title', 'Shipment Visibility: ' . $shipment->tracking_number)

@section('content')
<div class="space-y-6">
    
    <!-- Top Action Row -->
    <div class="flex items-center justify-between">
        <a href="{{ route('dashboard.shipments') }}" class="text-xs text-slate-400 hover:text-white flex items-center gap-1.5 transition">
            <i class="fa-solid fa-arrow-left"></i> Kembali ke Daftar
        </a>
        <div class="flex items-center gap-2">
            <a href="/?tracking_number={{ $shipment->tracking_number }}" target="_blank" class="px-3 py-1.5 rounded-xl bg-white/5 hover:bg-white/10 text-cyan-400 text-xs font-semibold border border-white/10 transition flex items-center gap-1.5">
                <i class="fa-solid fa-share-nodes"></i> Link Tracker Publik
            </a>
        </div>
    </div>

    <!-- Main Grid: Map & Details -->
    <div class="grid grid-cols-12 gap-6 items-start">
        
        <!-- Left: Map & Timeline (Col 8) -->
        <div class="col-span-12 lg:col-span-8 space-y-6">
            
            <!-- Leaflet.js Map -->
            <div class="glass-panel rounded-2xl overflow-hidden shadow-2xl">
                <div class="p-4 border-b border-white/10 flex items-center justify-between bg-white/[0.01]">
                    <div class="flex items-center gap-2 text-sm font-bold text-white">
                        <i class="fa-solid fa-map-location-dot text-blue-400"></i>
                        <span>Peta Rute & Titik Checkpoint</span>
                    </div>
                    <span class="text-[11px] text-slate-400">Leaflet.js &bull; OpenStreetMap</span>
                </div>
                <div id="shipmentDetailMap" class="w-full h-80 z-10"></div>
            </div>

            <!-- Chronological Timeline -->
            <div class="glass-panel p-6 rounded-2xl shadow-xl">
                <div class="flex items-center justify-between mb-6 border-b border-white/10 pb-4">
                    <h3 class="text-sm font-bold text-white flex items-center gap-2">
                        <i class="fa-solid fa-timeline text-cyan-400"></i> Riwayat Perjalanan Kargo
                    </h3>
                    <span class="text-xs text-slate-400">{{ count($shipment->trackingEvents) }} Events</span>
                </div>

                <div class="relative border-l-2 border-slate-700 ml-4 space-y-6">
                    @forelse($shipment->trackingEvents as $idx => $ev)
                        <div class="relative pl-6">
                            <div class="absolute -left-[9px] top-1 w-4 h-4 rounded-full {{ $ev->status === 'exception' ? 'bg-rose-500' : ($idx === 0 ? 'bg-blue-500' : 'bg-slate-700') }} border-2 border-slate-950"></div>
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="font-bold text-sm text-white">{{ $ev->event_title }}</span>
                                    <span class="text-[11px] text-slate-400 font-mono">&bull; {{ $ev->occurred_at->isoFormat('D MMM Y, HH:mm') }} WIB</span>
                                </div>
                                <p class="text-xs text-slate-300 mt-1">{{ $ev->description }}</p>
                                @if($ev->latitude && $ev->longitude)
                                    <span class="text-[10px] text-cyan-400 font-mono block mt-0.5">GPS: {{ $ev->latitude }}, {{ $ev->longitude }}</span>
                                @endif
                            </div>
                        </div>
                    @empty
                        <p class="text-xs text-slate-400">Belum ada riwayat tracking.</p>
                    @endforelse
                </div>
            </div>

            <!-- Operator Action Panels (Append-Only Event & Report Exception) -->
            @if(in_array(auth()->user()->role, ['admin', 'operator']) && !in_array($shipment->status, ['delivered', 'cancelled']))
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    
                    <!-- Form 1: Add Tracking Event -->
                    <div class="glass-panel p-5 rounded-2xl shadow-xl">
                        <h4 class="text-xs font-bold text-blue-400 uppercase tracking-wider mb-3 flex items-center gap-2">
                            <i class="fa-solid fa-plus-circle"></i> Tambah Tracking Event
                        </h4>
                        <form action="{{ route('dashboard.shipments.tracking', $shipment->id) }}" method="POST" class="space-y-3 text-xs">
                            @csrf
                            <div>
                                <label class="block text-slate-300 mb-1">Status Baru</label>
                                <select name="status" class="w-full py-2 px-3 rounded-xl bg-slate-900 border border-white/10 text-white">
                                    <option value="in_transit">IN_TRANSIT (Dalam Perjalanan)</option>
                                    <option value="arrived_at_facility">ARRIVED_AT_FACILITY (Tiba di Fasilitas)</option>
                                    <option value="out_for_delivery">OUT_FOR_DELIVERY (Sedang Diantar)</option>
                                    <option value="delivered">DELIVERED (Terkirim)</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-slate-300 mb-1">Judul Event</label>
                                <input type="text" name="event_title" placeholder="contoh: Tiba di Pelabuhan Tanjung Perak" required class="w-full py-2 px-3 rounded-xl bg-slate-900 border border-white/10 text-white">
                            </div>
                            <div>
                                <label class="block text-slate-300 mb-1">Deskripsi Tambahan</label>
                                <textarea name="description" rows="2" placeholder="Kondisi kargo, verifikasi manifest..." class="w-full py-2 px-3 rounded-xl bg-slate-900 border border-white/10 text-white"></textarea>
                            </div>
                            <button type="submit" class="w-full py-2 rounded-xl bg-blue-600 hover:bg-blue-500 text-white font-bold transition">
                                Simpan Event Tracking
                            </button>
                        </form>
                    </div>

                    <!-- Form 2: Report Exception -->
                    <div class="glass-panel p-5 rounded-2xl shadow-xl">
                        <h4 class="text-xs font-bold text-rose-400 uppercase tracking-wider mb-3 flex items-center gap-2">
                            <i class="fa-solid fa-triangle-exclamation"></i> Laporkan Kendala / Exception
                        </h4>
                        <form action="{{ route('dashboard.shipments.exceptions', $shipment->id) }}" method="POST" class="space-y-3 text-xs">
                            @csrf
                            <div>
                                <label class="block text-slate-300 mb-1">Jenis Kendala (UAS Type)</label>
                                <select name="exception_type" class="w-full py-2 px-3 rounded-xl bg-slate-900 border border-white/10 text-white">
                                    <option value="customs_hold">Customs Hold (Tertahan Bea Cukai)</option>
                                    <option value="weather_delay">Weather Delay (Cuaca Buruk / Gelombang)</option>
                                    <option value="port_congestion">Port Congestion (Kepadatan Pelabuhan)</option>
                                    <option value="damaged">Damaged (Kerusakan Barang)</option>
                                    <option value="misrouted">Misrouted (Salah Rute)</option>
                                    <option value="documentation_issue">Documentation Issue (Kendala Dokumen)</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-slate-300 mb-1">Severity</label>
                                <select name="severity" class="w-full py-2 px-3 rounded-xl bg-slate-900 border border-white/10 text-white">
                                    <option value="low">LOW</option>
                                    <option value="medium" selected>MEDIUM</option>
                                    <option value="high">HIGH</option>
                                    <option value="critical">CRITICAL</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-slate-300 mb-1">Alasan Kendala</label>
                                <textarea name="reason" rows="2" required placeholder="Jelaskan alasan kendala..." class="w-full py-2 px-3 rounded-xl bg-slate-900 border border-white/10 text-white"></textarea>
                            </div>
                            <button type="submit" class="w-full py-2 rounded-xl bg-rose-600 hover:bg-rose-500 text-white font-bold transition">
                                Laporkan Exception
                            </button>
                        </form>
                    </div>

                </div>
            @endif

        </div>

        <!-- Right: Status, Weather & Items (Col 4) -->
        <div class="col-span-12 lg:col-span-4 space-y-6">
            
            <!-- Status & Metrics Card -->
            <div class="glass-panel p-6 rounded-2xl shadow-xl">
                <span class="text-xs text-slate-400 uppercase tracking-wider font-semibold block mb-2">Kondisi Saat Ini</span>
                <div class="flex items-center justify-between mb-4">
                    <span class="text-2xl font-black text-white font-mono">{{ $shipment->tracking_number }}</span>
                    <span class="px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider border {{ $shipment->status === 'delivered' ? 'bg-emerald-500/20 text-emerald-300 border-emerald-500/30' : ($shipment->status === 'exception' ? 'bg-rose-500/20 text-rose-300 border-rose-500/30' : 'bg-blue-500/20 text-blue-300 border-blue-500/30') }}">
                        {{ str_replace('_', ' ', $shipment->status) }}
                    </span>
                </div>

                <div class="space-y-2.5 text-xs text-slate-300 bg-slate-900/60 p-4 rounded-xl border border-white/5">
                    <div class="flex justify-between">
                        <span class="text-slate-400">Carrier:</span>
                        <span class="font-bold text-white">{{ $shipment->carrier->name ?? 'N/A' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-400">Jenis Layanan:</span>
                        <span class="font-semibold uppercase text-cyan-400 font-mono">{{ $shipment->service_type }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-400">Berat & Volume:</span>
                        <span class="font-semibold font-mono">{{ $shipment->total_weight }} kg &bull; {{ $shipment->total_volume }} m&sup3;</span>
                    </div>
                    <div class="border-t border-white/5 pt-2 flex justify-between items-center">
                        <span class="text-slate-400">Status Keterlambatan:</span>
                        @if($shipment->is_delayed)
                            <span class="text-rose-400 font-bold font-mono">DELAY {{ $shipment->delay_hours }}h ({{ $shipment->delay_severity }})</span>
                        @else
                            <span class="text-emerald-400 font-semibold font-mono">ON-TIME</span>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Live Weather Widget (Open-Meteo) -->
            <div class="glass-panel p-6 rounded-2xl shadow-xl">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs text-slate-400 font-semibold uppercase tracking-wider">
                        <i class="fa-solid fa-cloud-sun text-cyan-400 mr-1"></i> Live Weather Checkpoint
                    </span>
                    <span class="text-[10px] px-2 py-0.5 rounded-full {{ $weatherData['status'] === 'online' ? 'bg-emerald-500/20 text-emerald-300' : 'bg-slate-700 text-slate-400' }}">
                        {{ $weatherData['status'] === 'online' ? 'Open-Meteo' : 'Fallback' }}
                    </span>
                </div>
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <i class="fa-solid {{ $weatherData['icon'] }} text-3xl text-cyan-400"></i>
                        <div>
                            <span class="text-2xl font-black text-white">{{ $weatherData['temperature'] }}&deg;C</span>
                            <p class="text-xs text-slate-300">{{ $weatherData['condition'] }}</p>
                        </div>
                    </div>
                    <div class="text-right text-xs text-slate-400">
                        <p><i class="fa-solid fa-wind mr-1"></i> {{ $weatherData['windspeed'] }} km/h</p>
                    </div>
                </div>
            </div>

            <!-- Resolve Exception Box if Any Active -->
            @php $activeExceptions = $shipment->exceptions->where('status', '!=', 'resolved'); @endphp
            @if($activeExceptions->count() > 0 && in_array(auth()->user()->role, ['admin', 'operator']))
                <div class="glass-panel p-6 rounded-2xl shadow-xl border-amber-500/30">
                    <h4 class="text-xs font-bold text-amber-400 uppercase tracking-wider mb-3 flex items-center gap-2">
                        <i class="fa-solid fa-wrench"></i> Resolusi Kendala Aktif
                    </h4>
                    @foreach($activeExceptions as $actExc)
                        <div class="p-3 bg-slate-900/60 rounded-xl mb-3 text-xs border border-white/5">
                            <span class="font-bold text-rose-300 uppercase block mb-1">{{ str_replace('_', ' ', $actExc->exception_type) }}</span>
                            <p class="text-slate-300 mb-2">{{ $actExc->reason }}</p>
                            <form action="{{ route('dashboard.exceptions.resolve', $actExc->id) }}" method="POST" class="space-y-2">
                                @csrf
                                <input type="hidden" name="status" value="resolved">
                                <input type="text" name="resolution_notes" placeholder="Catatan resolusi..." required class="w-full py-1.5 px-3 rounded-lg bg-slate-900 border border-white/10 text-white text-xs">
                                <button type="submit" class="w-full py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-500 text-white font-bold transition text-xs">
                                    Selesaikan Kendala & Lanjutkan Kargo
                                </button>
                            </form>
                        </div>
                    @endforeach
                </div>
            @endif

            <!-- Items List -->
            <div class="glass-panel p-6 rounded-2xl shadow-xl">
                <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-3">Manifest Barang</h4>
                <div class="space-y-2 text-xs">
                    @foreach($shipment->items as $it)
                        <div class="p-2.5 rounded-xl bg-slate-900/40 border border-white/5 flex justify-between items-center">
                            <div>
                                <span class="font-bold text-slate-200 block">{{ $it->item_name }}</span>
                                <span class="text-[10px] text-slate-400">{{ $it->item_category }}</span>
                            </div>
                            <div class="text-right">
                                <span class="font-mono text-cyan-400 font-bold">{{ $it->quantity }}x</span>
                                <span class="block text-[10px] text-slate-400">{{ $it->weight }} kg</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

        </div>

    </div>

</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const mapData = @json($mapData);
        const map = L.map('shipmentDetailMap').setView([mapData.current_position.latitude, mapData.current_position.longitude], 6);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors',
            maxZoom: 18,
        }).addTo(map);

        if (mapData.polyline && mapData.polyline.length > 1) {
            const polyline = L.polyline(mapData.polyline, {
                color: '#06b6d4',
                weight: 3.5,
                opacity: 0.8,
                dashArray: '6, 8'
            }).addTo(map);
            map.fitBounds(polyline.getBounds(), { padding: [40, 40] });
        }

        mapData.points.forEach(point => {
            const marker = L.circleMarker([point.latitude, point.longitude], {
                radius: point.type === 'checkpoint' ? 6 : 9,
                fillColor: point.color,
                color: '#ffffff',
                weight: 2,
                opacity: 1,
                fillOpacity: 0.9
            }).addTo(map);

            marker.bindPopup(`<strong>${point.title}</strong><br><span style="font-size: 11px;">${point.location || ''}</span>`);
        });
    });
</script>
@endsection

