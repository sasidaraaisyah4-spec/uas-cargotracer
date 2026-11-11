<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CargoTrace - Sistem Pelacakan Kargo Real-time</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap');
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="bg-slate-900 text-slate-100 min-h-screen flex flex-col justify-between">

    <!-- Header Navigation -->
    <header class="border-b border-slate-800 bg-slate-900/80 backdrop-blur sticky top-0 z-50">
        <div class="max-w-6xl mx-auto px-4 py-4 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-blue-600 flex items-center justify-center text-white shadow-lg shadow-blue-500/30">
                    <i class="fa-solid fa-truck-fast text-lg"></i>
                </div>
                <div>
                    <span class="text-xl font-extrabold tracking-tight text-white">Cargo<span class="text-blue-500">Trace</span></span>
                    <span class="block text-xs text-slate-400 font-medium">Logistics & Tracking System</span>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <a href="/api/shipments" target="_blank" class="text-xs bg-slate-800 hover:bg-slate-700 text-slate-300 px-3 py-2 rounded-lg border border-slate-700 transition">
                    <i class="fa-solid fa-code mr-1.5 text-blue-400"></i> API Docs
                </a>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="max-w-4xl mx-auto px-4 py-10 w-full flex-1">
        
        <!-- Hero & Search Box -->
        <div class="text-center mb-10">
            <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-semibold bg-blue-500/10 text-blue-400 border border-blue-500/20 mb-4">
                <i class="fa-solid fa-satellite-dish animate-pulse"></i> Real-time Cargo Tracking
            </span>
            <h1 class="text-3xl md:text-5xl font-extrabold text-white tracking-tight mb-4">
                Lacak Status Pengiriman Kargo
            </h1>
            <p class="text-slate-400 text-sm md:text-base max-w-xl mx-auto">
                Masukkan nomor resi pengiriman untuk memantau perjalanan dan lokasi terkini paket Anda secara akurat.
            </p>

            <!-- Search Form -->
            <form action="/" method="GET" class="mt-8 max-w-2xl mx-auto">
                <div class="relative flex items-center shadow-2xl rounded-2xl bg-slate-800/90 border border-slate-700 p-2 focus-within:border-blue-500 focus-within:ring-2 focus-within:ring-blue-500/20 transition">
                    <i class="fa-solid fa-magnifying-glass text-slate-400 ml-3 mr-2 text-lg"></i>
                    <input 
                        type="text" 
                        name="tracking_number" 
                        value="{{ $trackingNumber ?? '' }}"
                        placeholder="Masukkan nomor resi, contoh: CT-20261107-JKTSBY001" 
                        class="w-full bg-transparent border-0 text-white placeholder-slate-400 focus:outline-none text-sm md:text-base px-2 py-2"
                        required
                    />
                    <button type="submit" class="bg-blue-600 hover:bg-blue-500 text-white font-semibold px-5 py-2.5 rounded-xl transition shadow-lg shadow-blue-600/30 flex items-center gap-2 whitespace-nowrap text-sm">
                        <span>Lacak</span>
                        <i class="fa-solid fa-arrow-right text-xs"></i>
                    </button>
                </div>
                <!-- Demo Quick Link -->
                <div class="mt-3 text-xs text-slate-400 flex items-center justify-center gap-2">
                    <span>Resi Demo:</span>
                    <a href="/?tracking_number=CT-20261107-JKTSBY001" class="text-blue-400 hover:text-blue-300 underline font-mono">
                        CT-20261107-JKTSBY001
                    </a>
                </div>
            </form>
        </div>

        @if($searched)
            @if($shipment)
                <!-- Tracking Result Container -->
                <div class="bg-slate-800/80 border border-slate-700/80 rounded-2xl overflow-hidden shadow-xl backdrop-blur">
                    
                    <!-- Header Info -->
                    <div class="p-6 border-b border-slate-700 flex flex-wrap items-center justify-between gap-4 bg-slate-800/50">
                        <div>
                            <div class="flex items-center gap-3">
                                <h2 class="text-xl font-bold text-white tracking-tight font-mono">{{ $shipment->tracking_number }}</h2>
                                @php
                                    $badgeColors = [
                                        'pending' => 'bg-yellow-500/20 text-yellow-400 border-yellow-500/30',
                                        'in_transit' => 'bg-blue-500/20 text-blue-400 border-blue-500/30',
                                        'out_for_delivery' => 'bg-purple-500/20 text-purple-400 border-purple-500/30',
                                        'delivered' => 'bg-emerald-500/20 text-emerald-400 border-emerald-500/30',
                                        'cancelled' => 'bg-rose-500/20 text-rose-400 border-rose-500/30',
                                    ];
                                    $badge = $badgeColors[$shipment->status] ?? 'bg-slate-700 text-slate-300 border-slate-600';
                                @endphp
                                <span class="px-3 py-1 rounded-full text-xs font-semibold uppercase tracking-wider border {{ $badge }}">
                                    {{ str_replace('_', ' ', $shipment->status) }}
                                </span>
                            </div>
                            <p class="text-xs text-slate-400 mt-1">
                                Kurir: <span class="text-slate-200 font-medium">{{ $shipment->carrier->name ?? 'CargoTrace Express' }}</span> &bull; 
                                Layanan: <span class="text-slate-200 font-medium uppercase">{{ $shipment->service_type }}</span>
                            </p>
                        </div>
                        <div class="text-right">
                            <span class="text-xs text-slate-400 block">Estimasi Tiba</span>
                            <span class="text-sm font-semibold text-slate-200">
                                {{ $shipment->estimated_delivery_at ? \Carbon\Carbon::parse($shipment->estimated_delivery_at)->isoFormat('D MMMM Y') : 'Dalam Konfirmasi' }}
                            </span>
                        </div>
                    </div>

                    <!-- Origin to Destination Summary -->
                    <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6 border-b border-slate-700">
                        <!-- Sender -->
                        <div class="bg-slate-900/50 p-4 rounded-xl border border-slate-800">
                            <span class="text-xs font-semibold text-blue-400 uppercase tracking-wider flex items-center gap-1.5 mb-2">
                                <i class="fa-solid fa-circle-dot"></i> Asal Pengiriman
                            </span>
                            <p class="font-bold text-white text-base">{{ $shipment->sender_name }}</p>
                            <p class="text-xs text-slate-300 mt-1">{{ $shipment->sender_address }}</p>
                            <p class="text-xs text-slate-400 mt-1"><i class="fa-solid fa-phone mr-1"></i> {{ $shipment->sender_phone }}</p>
                        </div>

                        <!-- Recipient -->
                        <div class="bg-slate-900/50 p-4 rounded-xl border border-slate-800">
                            <span class="text-xs font-semibold text-emerald-400 uppercase tracking-wider flex items-center gap-1.5 mb-2">
                                <i class="fa-solid fa-location-dot"></i> Tujuan Penerima
                            </span>
                            <p class="font-bold text-white text-base">{{ $shipment->recipient_name }}</p>
                            <p class="text-xs text-slate-300 mt-1">{{ $shipment->recipient_address }}</p>
                            <p class="text-xs text-slate-400 mt-1"><i class="fa-solid fa-phone mr-1"></i> {{ $shipment->recipient_phone }}</p>
                        </div>
                    </div>

                    <!-- Items Summary -->
                    <div class="p-6 border-b border-slate-700">
                        <h3 class="text-sm font-semibold text-slate-300 mb-3 flex items-center gap-2">
                            <i class="fa-solid fa-boxes-packing text-blue-400"></i> Rincian Paket (Total Berat: {{ $shipment->total_weight }} kg)
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            @foreach($shipment->items as $item)
                                <div class="bg-slate-900/40 p-3 rounded-lg border border-slate-800 flex justify-between items-center text-xs">
                                    <div>
                                        <p class="font-semibold text-slate-200">{{ $item->item_name }}</p>
                                        <p class="text-slate-400 text-[11px]">{{ $item->item_category }} &bull; {{ $item->description }}</p>
                                    </div>
                                    <div class="text-right whitespace-nowrap">
                                        <span class="px-2 py-0.5 rounded bg-slate-800 text-slate-300 font-mono">{{ $item->quantity }} pcs</span>
                                        <span class="text-slate-400 block text-[11px] mt-0.5">{{ $item->weight }} kg</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Tracking Timeline -->
                    <div class="p-6">
                        <h3 class="text-sm font-semibold text-slate-300 mb-6 flex items-center gap-2">
                            <i class="fa-solid fa-timeline text-blue-400"></i> Riwayat Perjalanan Paket
                        </h3>
                        
                        <div class="relative border-l-2 border-slate-700 ml-4 space-y-6">
                            @forelse($shipment->trackingEvents as $index => $event)
                                <div class="relative pl-6">
                                    <!-- Dot Indicator -->
                                    <div class="absolute -left-[9px] top-1 w-4 h-4 rounded-full {{ $index === 0 ? 'bg-blue-500 ring-4 ring-blue-500/20' : 'bg-slate-700' }} border-2 border-slate-900"></div>

                                    <div>
                                        <div class="flex flex-wrap items-center gap-2">
                                            <span class="font-bold text-sm {{ $index === 0 ? 'text-blue-400' : 'text-slate-200' }}">
                                                {{ $event->event_title }}
                                            </span>
                                            <span class="text-[11px] text-slate-400">
                                                &bull; {{ \Carbon\Carbon::parse($event->occurred_at)->isoFormat('D MMM Y, HH:mm') }} WIB
                                            </span>
                                        </div>
                                        @if($event->location)
                                            <p class="text-xs text-slate-400 mt-0.5">
                                                <i class="fa-solid fa-map-pin mr-1 text-slate-500"></i> {{ $event->location->name }} ({{ $event->location->city }})
                                            </p>
                                        @endif
                                        <p class="text-xs text-slate-300 mt-1 leading-relaxed">
                                            {{ $event->description }}
                                        </p>
                                    </div>
                                </div>
                            @empty
                                <p class="text-xs text-slate-500 pl-4">Belum ada riwayat timeline.</p>
                            @endforelse
                        </div>
                    </div>

                </div>
            @else
                <!-- Not Found State -->
                <div class="bg-slate-800/60 border border-rose-500/30 rounded-2xl p-8 text-center max-w-lg mx-auto">
                    <div class="w-12 h-12 rounded-full bg-rose-500/10 text-rose-400 flex items-center justify-center mx-auto mb-3">
                        <i class="fa-solid fa-triangle-exclamation text-xl"></i>
                    </div>
                    <h3 class="text-lg font-bold text-white mb-1">Nomor Resi Tidak Ditemukan</h3>
                    <p class="text-xs text-slate-400 leading-relaxed">
                        Resi <span class="font-mono text-rose-300 font-semibold">{{ $trackingNumber }}</span> belum terdaftar di sistem. Periksa kembali nomor resi yang Anda masukkan.
                    </p>
                </div>
            @endif
        @endif

    </main>

    <!-- Footer -->
    <footer class="border-t border-slate-800 py-6 text-center text-xs text-slate-400">
        <p>&copy; 2026 CargoTrace System. All rights reserved.</p>
    </footer>

</body>
</html>
