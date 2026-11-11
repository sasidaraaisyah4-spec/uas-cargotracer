<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CargoTrace — Global Shipment Visibility & Tracking Platform</title>

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- FontAwesome 6 Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Leaflet.js -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap');
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .glass-panel {
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.08);
        }
        .glass-panel-hover:hover {
            background: rgba(255, 255, 255, 0.05);
            border-color: rgba(255, 255, 255, 0.15);
        }
    </style>
</head>
<body class="bg-slate-950 text-slate-100 min-h-screen flex flex-col justify-between selection:bg-blue-600 selection:text-white">

    <!-- Ambient Glow Background Decoration -->
    <div class="fixed -top-40 -left-40 w-[600px] h-[600px] bg-blue-600/10 rounded-full blur-[140px] pointer-events-none"></div>
    <div class="fixed top-1/2 -right-40 w-[500px] h-[500px] bg-cyan-500/10 rounded-full blur-[140px] pointer-events-none"></div>

    <!-- Header Navigation -->
    <header class="border-b border-white/10 bg-slate-950/70 backdrop-blur-xl sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-6 py-4 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-blue-600 to-cyan-500 flex items-center justify-center text-white shadow-lg shadow-blue-500/25">
                    <i class="fa-solid fa-truck-fast text-lg"></i>
                </div>
                <div>
                    <span class="text-xl font-extrabold tracking-tight text-white">Cargo<span class="text-blue-500">Trace</span></span>
                    <span class="block text-[11px] text-slate-400 font-medium">Global Shipment Visibility Platform</span>
                </div>
            </div>
            <div class="flex items-center gap-3 text-xs">
                @auth
                    <a href="{{ route('dashboard') }}" class="px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-500 text-white font-semibold transition shadow-lg shadow-blue-600/30 flex items-center gap-2">
                        <i class="fa-solid fa-gauge"></i>
                        <span>Dashboard</span>
                    </a>
                @else
                    <a href="{{ route('login') }}" class="px-4 py-2 rounded-xl bg-white/5 hover:bg-white/10 text-slate-200 border border-white/10 transition font-medium">
                        Log In
                    </a>
                    <a href="{{ route('register') }}" class="px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-500 text-white font-semibold transition shadow-lg shadow-blue-600/30">
                        Register
                    </a>
                @endauth
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-6 py-10 w-full flex-1">
        
        <!-- Hero Section -->
        <div class="text-center mb-12">
            <span class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full text-xs font-semibold bg-blue-500/10 text-blue-400 border border-blue-500/20 mb-4 shadow-inner">
                <i class="fa-solid fa-satellite-dish animate-pulse"></i> Real-time Global Cargo Tracking
            </span>
            <h1 class="text-4xl md:text-6xl font-extrabold text-white tracking-tight leading-tight max-w-3xl mx-auto mb-4">
                Global Shipment Visibility & Tracking Platform
            </h1>
            <p class="text-slate-400 text-sm md:text-base max-w-2xl mx-auto">
                Pantau pergerakan kargo maritim, darat, dan udara dengan presisi GPS, estimasi ETA deterministik, serta deteksi keterlambatan berbasis aturan bisnis.
            </p>

            <!-- Search Form -->
            <form action="{{ route('tracking.home') }}" method="GET" class="mt-8 max-w-2xl mx-auto">
                <div class="relative flex items-center glass-panel rounded-2xl p-2.5 shadow-2xl focus-within:border-blue-500 focus-within:ring-2 focus-within:ring-blue-500/20 transition">
                    <i class="fa-solid fa-magnifying-glass text-slate-400 ml-3 mr-2 text-lg"></i>
                    <input 
                        type="text" 
                        name="tracking_number" 
                        value="{{ $trackingNumber ?? '' }}"
                        placeholder="Masukkan nomor resi kargo (contoh: CT-20261107-JKTSBY001)" 
                        class="w-full bg-transparent border-0 text-white placeholder-slate-400 focus:outline-none text-sm md:text-base px-2 py-1"
                        required
                    />
                    <button type="submit" class="bg-blue-600 hover:bg-blue-500 text-white font-semibold px-6 py-3 rounded-xl transition shadow-lg shadow-blue-600/30 flex items-center gap-2 whitespace-nowrap text-sm">
                        <span>Lacak Kargo</span>
                        <i class="fa-solid fa-arrow-right text-xs"></i>
                    </button>
                </div>
                <!-- Quick Demo Tags -->
                <div class="mt-4 flex flex-wrap items-center justify-center gap-2 text-xs text-slate-400">
                    <span>Coba Resi Demo:</span>
                    <a href="/?tracking_number=CT-20261107-JKTSBY001" class="px-2.5 py-1 rounded-lg bg-white/5 hover:bg-white/10 text-cyan-400 font-mono border border-white/10 transition">
                        CT-20261107-JKTSBY001 (On-Time)
                    </a>
                    <a href="/?tracking_number=CT-20261108-SGSUB999" class="px-2.5 py-1 rounded-lg bg-white/5 hover:bg-white/10 text-rose-400 font-mono border border-white/10 transition">
                        CT-20261108-SGSUB999 (Delayed & Exception)
                    </a>
                    <a href="/?tracking_number=CT-20261101-JKTSUB888" class="px-2.5 py-1 rounded-lg bg-white/5 hover:bg-white/10 text-emerald-400 font-mono border border-white/10 transition">
                        CT-20261101-JKTSUB888 (Delivered)
                    </a>
                </div>
            </form>
        </div>

        @if($searched)
            @if($shipment)
                <!-- 12-Column Responsive Bento Grid Layout -->
                <div class="grid grid-cols-12 gap-6 items-start">
                    
                    <!-- BENTO CARD 1: Interactive Leaflet.js Map (Col 8) -->
                    <div class="col-span-12 lg:col-span-8 glass-panel rounded-2xl overflow-hidden shadow-2xl flex flex-col">
                        <div class="p-5 border-b border-white/10 flex items-center justify-between bg-white/[0.01]">
                            <div class="flex items-center gap-2.5">
                                <i class="fa-solid fa-map-location-dot text-blue-400 text-lg"></i>
                                <span class="font-bold text-white text-base">Live Route & Checkpoints Map</span>
                            </div>
                            <span class="text-xs text-slate-400 flex items-center gap-1.5">
                                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                                OpenStreetMap &bull; Leaflet.js
                            </span>
                        </div>
                        <div id="shipmentMap" class="w-full h-[400px] z-10"></div>
                        <div class="p-4 bg-slate-900/40 border-t border-white/10 text-xs text-slate-400 flex flex-wrap items-center justify-between gap-3">
                            <div class="flex items-center gap-4">
                                <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-full bg-blue-500"></span> Origin</span>
                                <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-full bg-cyan-400"></span> Checkpoints</span>
                                <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-full bg-emerald-500"></span> Destination</span>
                            </div>
                            <span>Peta rute garis terhubung linier Origin &rarr; Checkpoints &rarr; Destination</span>
                        </div>
                    </div>

                    <!-- BENTO CARD 2: Current Status, ETA Comparison, Delay Severity & Realtime Weather (Col 4) -->
                    <div class="col-span-12 lg:col-span-4 space-y-6">
                        <!-- Status & ETA Box -->
                        <div class="glass-panel p-6 rounded-2xl shadow-2xl relative overflow-hidden">
                            <div class="flex items-center justify-between mb-4">
                                <span class="text-xs text-slate-400 uppercase tracking-wider font-semibold">Status Kargo</span>
                                @php
                                    $statusColors = [
                                        'created' => 'bg-slate-700 text-slate-300 border-slate-600',
                                        'picked_up' => 'bg-indigo-500/20 text-indigo-300 border-indigo-500/30',
                                        'in_transit' => 'bg-blue-500/20 text-blue-300 border-blue-500/30',
                                        'out_for_delivery' => 'bg-purple-500/20 text-purple-300 border-purple-500/30',
                                        'exception' => 'bg-rose-500/20 text-rose-300 border-rose-500/30',
                                        'delivered' => 'bg-emerald-500/20 text-emerald-300 border-emerald-500/30',
                                    ];
                                    $badge = $statusColors[$shipment->status] ?? 'bg-slate-700 text-slate-300 border-slate-600';
                                @endphp
                                <span class="px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider border {{ $badge }}">
                                    {{ str_replace('_', ' ', $shipment->status) }}
                                </span>
                            </div>

                            <p class="text-xl font-black text-white font-mono tracking-tight mb-2">{{ $shipment->tracking_number }}</p>
                            <p class="text-xs text-slate-400 mb-6">
                                Carrier: <span class="text-slate-200 font-semibold">{{ $shipment->carrier->name ?? 'CargoTrace Express' }}</span> &bull; 
                                Layanan: <span class="text-slate-200 font-semibold uppercase">{{ $shipment->service_type }}</span>
                            </p>

                            <!-- ETA Comparison -->
                            <div class="space-y-3 p-4 rounded-xl bg-slate-900/60 border border-white/5 text-xs">
                                <div class="flex justify-between items-center">
                                    <span class="text-slate-400">Expected Arrival:</span>
                                    <span class="font-semibold text-slate-200 font-mono">
                                        {{ $shipment->expected_arrival_at ? $shipment->expected_arrival_at->isoFormat('D MMM Y, HH:mm') : 'N/A' }}
                                    </span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-slate-400">Estimated Delivery (ETA):</span>
                                    <span class="font-semibold text-blue-400 font-mono">
                                        {{ $shipment->estimated_delivery_at ? $shipment->estimated_delivery_at->isoFormat('D MMM Y, HH:mm') : 'N/A' }}
                                    </span>
                                </div>
                                <div class="border-t border-white/5 pt-2 flex justify-between items-center">
                                    <span class="text-slate-400">Delay Evaluation:</span>
                                    @if($shipment->is_delayed)
                                        <span class="px-2 py-0.5 rounded text-[11px] font-bold bg-rose-500/20 text-rose-300 border border-rose-500/30">
                                            DELAY {{ $shipment->delay_hours }}h ({{ $shipment->delay_severity }})
                                        </span>
                                    @else
                                        <span class="px-2 py-0.5 rounded text-[11px] font-bold bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">
                                            ON TIME
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Real-time Weather Widget (Open-Meteo) -->
                        <div class="glass-panel p-6 rounded-2xl shadow-2xl">
                            <div class="flex items-center justify-between mb-3">
                                <span class="text-xs text-slate-400 uppercase tracking-wider font-semibold flex items-center gap-1.5">
                                    <i class="fa-solid fa-cloud-sun text-cyan-400"></i> Weather at Cargo Position
                                </span>
                                <span class="text-[10px] px-2 py-0.5 rounded-full {{ $weatherData['status'] === 'online' ? 'bg-emerald-500/20 text-emerald-300' : 'bg-slate-700 text-slate-400' }}">
                                    {{ $weatherData['status'] === 'online' ? 'Live Open-Meteo' : 'Offline Mode' }}
                                </span>
                            </div>

                            <div class="flex items-center justify-between pt-2">
                                <div>
                                    <div class="flex items-center gap-3">
                                        <i class="fa-solid {{ $weatherData['icon'] }} text-3xl text-cyan-400"></i>
                                        <div>
                                            <span class="text-3xl font-extrabold text-white">{{ $weatherData['temperature'] }}&deg;C</span>
                                            <p class="text-xs text-slate-300 font-medium">{{ $weatherData['condition'] }}</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="text-right text-xs text-slate-400">
                                    <p><i class="fa-solid fa-wind mr-1"></i> {{ $weatherData['windspeed'] }} km/h</p>
                                    <p class="mt-1 text-[11px]">Direction: {{ $weatherData['winddirection'] }}&deg;</p>
                                </div>
                            </div>
                            @if($weatherData['is_adverse'])
                                <div class="mt-3 p-2.5 rounded-lg bg-amber-500/10 border border-amber-500/30 text-amber-300 text-[11px] flex items-center gap-2">
                                    <i class="fa-solid fa-triangle-exclamation"></i>
                                    <span>Peringatan: Cuaca buruk terdeteksi di koordinat posisi kargo.</span>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- BENTO CARD 3: Chronological Timeline & Exceptions (Col 7) -->
                    <div class="col-span-12 lg:col-span-7 glass-panel p-6 rounded-2xl shadow-2xl">
                        <div class="flex items-center justify-between mb-6 border-b border-white/10 pb-4">
                            <h3 class="text-base font-bold text-white flex items-center gap-2">
                                <i class="fa-solid fa-timeline text-blue-400"></i> Chronological Tracking Timeline
                            </h3>
                            <span class="text-xs text-slate-400">{{ count($shipment->trackingEvents) }} Events Recorded</span>
                        </div>

                        <div class="relative border-l-2 border-slate-700 ml-4 space-y-6">
                            @forelse($shipment->trackingEvents as $index => $event)
                                <div class="relative pl-6">
                                    <!-- Indicator Dot -->
                                    <div class="absolute -left-[9px] top-1 w-4 h-4 rounded-full {{ $event->status === 'exception' ? 'bg-rose-500 ring-4 ring-rose-500/20' : ($index === 0 ? 'bg-blue-500 ring-4 ring-blue-500/20' : 'bg-slate-700') }} border-2 border-slate-950"></div>

                                    <div>
                                        <div class="flex flex-wrap items-center gap-2">
                                            <span class="font-bold text-sm {{ $event->status === 'exception' ? 'text-rose-400' : ($index === 0 ? 'text-blue-400' : 'text-slate-200') }}">
                                                {{ $event->event_title }}
                                            </span>
                                            <span class="text-[11px] text-slate-400 font-mono">
                                                &bull; {{ $event->occurred_at->isoFormat('D MMM Y, HH:mm') }} WIB
                                            </span>
                                        </div>
                                        <p class="text-xs text-slate-300 mt-1 leading-relaxed">
                                            {{ $event->description }}
                                        </p>
                                        @if($event->latitude && $event->longitude)
                                            <p class="text-[11px] text-slate-400 mt-1 font-mono">
                                                <i class="fa-solid fa-crosshairs mr-1"></i> Coord: {{ $event->latitude }}, {{ $event->longitude }}
                                            </p>
                                        @endif
                                    </div>
                                </div>
                            @empty
                                <p class="text-xs text-slate-400 pl-4">Belum ada event tracking.</p>
                            @endforelse
                        </div>

                        <!-- Active Exceptions Section -->
                        @if($shipment->exceptions->count() > 0)
                            <div class="mt-8 pt-6 border-t border-white/10">
                                <h4 class="text-xs font-bold text-rose-400 uppercase tracking-wider mb-3 flex items-center gap-2">
                                    <i class="fa-solid fa-triangle-exclamation"></i> Log Kendala & Exceptions
                                </h4>
                                <div class="space-y-3">
                                    @foreach($shipment->exceptions as $exc)
                                        <div class="p-3.5 rounded-xl bg-rose-500/10 border border-rose-500/30 text-xs">
                                            <div class="flex items-center justify-between mb-1">
                                                <span class="font-bold text-rose-300 uppercase">{{ str_replace('_', ' ', $exc->exception_type) }}</span>
                                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $exc->status === 'resolved' ? 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/30' : 'bg-rose-500/20 text-rose-300 border border-rose-500/30' }}">
                                                    {{ $exc->status }}
                                                </span>
                                            </div>
                                            <p class="text-slate-300">{{ $exc->reason }}</p>
                                            @if($exc->resolution_notes)
                                                <p class="text-[11px] text-emerald-400 mt-1.5"><i class="fa-solid fa-check mr-1"></i> Resolusi: {{ $exc->resolution_notes }}</p>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>

                    <!-- BENTO CARD 4: Cargo Items & Party Details (Col 5) -->
                    <div class="col-span-12 lg:col-span-5 space-y-6">
                        
                        <!-- Origin & Destination Info -->
                        <div class="glass-panel p-6 rounded-2xl shadow-2xl">
                            <h3 class="text-base font-bold text-white mb-4 flex items-center gap-2">
                                <i class="fa-solid fa-route text-cyan-400"></i> Route & Parties
                            </h3>

                            <div class="space-y-4 text-xs">
                                <div class="p-3.5 rounded-xl bg-slate-900/60 border border-white/5">
                                    <span class="text-blue-400 font-bold uppercase tracking-wider text-[11px] block mb-1">Pengirim (Origin)</span>
                                    <p class="font-bold text-white text-sm">{{ $shipment->sender_name }}</p>
                                    <p class="text-slate-400 mt-0.5">{{ $shipment->sender_address }}</p>
                                    <p class="text-slate-400 mt-1 font-mono"><i class="fa-solid fa-phone mr-1"></i> {{ $shipment->sender_phone }}</p>
                                </div>
                                <div class="p-3.5 rounded-xl bg-slate-900/60 border border-white/5">
                                    <span class="text-emerald-400 font-bold uppercase tracking-wider text-[11px] block mb-1">Penerima (Destination)</span>
                                    <p class="font-bold text-white text-sm">{{ $shipment->recipient_name }}</p>
                                    <p class="text-slate-400 mt-0.5">{{ $shipment->recipient_address }}</p>
                                    <p class="text-slate-400 mt-1 font-mono"><i class="fa-solid fa-phone mr-1"></i> {{ $shipment->recipient_phone }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Cargo Items List & Volume -->
                        <div class="glass-panel p-6 rounded-2xl shadow-2xl">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-base font-bold text-white flex items-center gap-2">
                                    <i class="fa-solid fa-boxes-stacked text-blue-400"></i> Cargo Manifest Items
                                </h3>
                                <span class="text-xs text-slate-400">{{ count($shipment->items) }} Item(s)</span>
                            </div>

                            <div class="space-y-2.5 max-h-60 overflow-y-auto pr-1">
                                @foreach($shipment->items as $item)
                                    <div class="p-3 rounded-xl bg-slate-900/40 border border-white/5 text-xs flex justify-between items-center">
                                        <div>
                                            <p class="font-semibold text-slate-200">{{ $item->item_name }}</p>
                                            <p class="text-[11px] text-slate-400">{{ $item->item_category }} &bull; {{ $item->description }}</p>
                                        </div>
                                        <div class="text-right">
                                            <span class="font-mono text-cyan-400 font-bold">{{ $item->quantity }} pcs</span>
                                            <span class="block text-[11px] text-slate-400 font-mono">{{ $item->weight }} kg</span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <div class="mt-4 pt-3 border-t border-white/10 grid grid-cols-2 gap-3 text-xs text-slate-300">
                                <div class="p-2.5 rounded-lg bg-slate-900/60">
                                    <span class="text-slate-400 text-[11px] block">Total Berat:</span>
                                    <span class="font-bold text-white font-mono">{{ $shipment->total_weight }} kg</span>
                                </div>
                                <div class="p-2.5 rounded-lg bg-slate-900/60">
                                    <span class="text-slate-400 text-[11px] block">Total Volume:</span>
                                    <span class="font-bold text-cyan-400 font-mono">{{ $shipment->total_volume }} CBM (m&sup3;)</span>
                                </div>
                            </div>
                        </div>

                    </div>

                </div>
            @else
                <!-- Resi Not Found State -->
                <div class="glass-panel p-10 rounded-2xl text-center max-w-lg mx-auto shadow-2xl border-rose-500/20">
                    <div class="w-14 h-14 rounded-full bg-rose-500/10 text-rose-400 flex items-center justify-center mx-auto mb-4 border border-rose-500/30">
                        <i class="fa-solid fa-triangle-exclamation text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-white mb-2">Nomor Resi Tidak Ditemukan</h3>
                    <p class="text-xs text-slate-400 leading-relaxed mb-6">
                        Nomor resi <span class="font-mono text-rose-300 font-bold">{{ $trackingNumber }}</span> belum terdaftar di sistem CargoTrace. Pastikan kode resi yang Anda masukkan benar.
                    </p>
                    <a href="/" class="text-xs text-blue-400 hover:text-blue-300 underline font-medium">Kembali ke Beranda</a>
                </div>
            @endif
        @endif

    </main>

    <!-- Footer -->
    <footer class="border-t border-white/5 py-6 px-6 text-center text-xs text-slate-400">
        <p>&copy; 2026 CargoTrace — Global Shipment Visibility & Tracking Platform (UAS Web Programming 2)</p>
    </footer>

    @if($searched && $shipment && $mapData)
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const mapData = @json($mapData);
            
            // Initialize Leaflet Map
            const map = L.map('shipmentMap').setView(
                [mapData.current_position.latitude, mapData.current_position.longitude], 
                6
            );

            // OpenStreetMap Tile Layer (with required attribution)
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
                maxZoom: 18,
            }).addTo(map);

            // Draw Linear Polyline: Origin -> Checkpoints -> Destination
            if (mapData.polyline && mapData.polyline.length > 1) {
                const polyline = L.polyline(mapData.polyline, {
                    color: '#06b6d4',
                    weight: 3.5,
                    opacity: 0.8,
                    dashArray: '6, 8',
                    lineJoin: 'round'
                }).addTo(map);

                map.fitBounds(polyline.getBounds(), { padding: [50, 50] });
            }

            // Add Markers
            mapData.points.forEach(point => {
                const marker = L.circleMarker([point.latitude, point.longitude], {
                    radius: point.type === 'checkpoint' ? 6 : 9,
                    fillColor: point.color,
                    color: '#ffffff',
                    weight: 2,
                    opacity: 1,
                    fillOpacity: 0.9
                }).addTo(map);

                marker.bindPopup(`
                    <div style="font-family: 'Plus Jakarta Sans', sans-serif; font-size: 12px; color: #0f172a;">
                        <strong style="display:block; margin-bottom: 2px;">${point.title}</strong>
                        <span>${point.location || ''}</span>
                        ${point.time ? `<div style="font-size: 10px; color: #64748b; margin-top: 4px;">${point.time}</div>` : ''}
                    </div>
                `);
            });
        });
    </script>
    @endif

</body>
</html>
