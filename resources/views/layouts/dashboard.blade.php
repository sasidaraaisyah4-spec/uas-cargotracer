<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Dashboard') — CargoTrace Platform</title>

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- FontAwesome 6 Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- Leaflet.js -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap');
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .glass-panel {
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.08);
        }
        .glass-panel-hover:hover {
            background: rgba(255, 255, 255, 0.06);
            border-color: rgba(255, 255, 255, 0.15);
        }
        .custom-scrollbar::-webkit-scrollbar { width: 5px; height: 5px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: rgba(15, 23, 42, 0.6); }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: rgba(255, 255, 255, 0.15); border-radius: 9999px; }
    </style>
</head>
<body class="bg-slate-950 text-slate-100 min-h-screen flex antialiased selection:bg-blue-600 selection:text-white">

    <!-- Ambient Glow Background Decoration -->
    <div class="fixed top-0 left-1/4 w-96 h-96 bg-blue-600/10 rounded-full blur-3xl pointer-events-none"></div>
    <div class="fixed bottom-10 right-10 w-96 h-96 bg-cyan-500/10 rounded-full blur-3xl pointer-events-none"></div>

    <!-- Fixed Glassmorphic Sidebar -->
    <aside class="w-64 bg-slate-900/60 backdrop-blur-2xl border-r border-white/10 flex flex-col justify-between fixed inset-y-0 z-40">
        <div>
            <!-- Brand Logo -->
            <div class="p-6 border-b border-white/10 flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-blue-600 to-cyan-500 flex items-center justify-center text-white shadow-lg shadow-blue-500/20">
                    <i class="fa-solid fa-truck-fast text-lg"></i>
                </div>
                <div>
                    <span class="text-xl font-extrabold tracking-tight text-white">Cargo<span class="text-blue-500">Trace</span></span>
                    <span class="block text-[11px] text-slate-400 font-medium">Shipment Visibility</span>
                </div>
            </div>

            <!-- Navigation Links -->
            <nav class="p-4 space-y-1.5 text-sm">
                <div class="px-3 py-2 text-[11px] font-bold uppercase tracking-wider text-slate-400">Main Menu</div>
                
                <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition {{ request()->routeIs('dashboard') ? 'bg-blue-600 text-white shadow-lg shadow-blue-600/30 font-semibold' : 'text-slate-300 hover:bg-white/5 hover:text-white' }}">
                    <i class="fa-solid fa-chart-pie w-5"></i>
                    <span>Dashboard</span>
                </a>

                <a href="{{ route('dashboard.shipments') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition {{ request()->routeIs('dashboard.shipments*') ? 'bg-blue-600 text-white shadow-lg shadow-blue-600/30 font-semibold' : 'text-slate-300 hover:bg-white/5 hover:text-white' }}">
                    <i class="fa-solid fa-boxes-packing w-5"></i>
                    <span>Shipments</span>
                </a>

                @if(in_array(auth()->user()->role, ['admin', 'operator']))
                <a href="{{ route('dashboard.shipments.create') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition {{ request()->routeIs('dashboard.shipments.create') ? 'bg-blue-600 text-white shadow-lg shadow-blue-600/30 font-semibold' : 'text-slate-300 hover:bg-white/5 hover:text-white' }}">
                    <i class="fa-solid fa-plus-circle w-5 text-emerald-400"></i>
                    <span>New Shipment</span>
                </a>
                @endif

                <div class="pt-4 px-3 py-2 text-[11px] font-bold uppercase tracking-wider text-slate-400">Tracking & Alerts</div>

                <a href="{{ route('tracking.home') }}" target="_blank" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-slate-300 hover:bg-white/5 hover:text-white transition">
                    <i class="fa-solid fa-satellite-dish w-5 text-blue-400"></i>
                    <span>Public Tracker</span>
                    <i class="fa-solid fa-arrow-up-right-from-square text-[10px] ml-auto text-slate-400"></i>
                </a>

                <a href="{{ route('dashboard.notifications') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition {{ request()->routeIs('dashboard.notifications') ? 'bg-blue-600 text-white shadow-lg shadow-blue-600/30 font-semibold' : 'text-slate-300 hover:bg-white/5 hover:text-white' }}">
                    <i class="fa-solid fa-bell w-5 text-amber-400"></i>
                    <span>Notifications</span>
                    @php $unreadCount = auth()->user()->unreadNotifications()->count(); @endphp
                    @if($unreadCount > 0)
                        <span class="ml-auto px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-500 text-slate-950">{{ $unreadCount }}</span>
                    @endif
                </a>
            </nav>
        </div>

        <!-- User Profile & Logout -->
        <div class="p-4 border-t border-white/10 bg-white/[0.02]">
            <div class="flex items-center justify-between mb-3 px-2">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-full bg-blue-600/30 border border-blue-500/40 flex items-center justify-center font-bold text-xs text-blue-400">
                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                    </div>
                    <div class="overflow-hidden">
                        <p class="text-xs font-bold text-white truncate max-w-[110px]">{{ auth()->user()->name }}</p>
                        <span class="text-[10px] px-1.5 py-0.5 rounded uppercase font-semibold tracking-wider {{ auth()->user()->role === 'admin' ? 'bg-rose-500/20 text-rose-300 border border-rose-500/30' : (auth()->user()->role === 'operator' ? 'bg-blue-500/20 text-blue-300 border border-blue-500/30' : 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/30') }}">
                            {{ auth()->user()->role }}
                        </span>
                    </div>
                </div>
            </div>
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="w-full text-xs flex items-center justify-center gap-2 py-2 px-3 rounded-xl bg-white/5 hover:bg-rose-500/20 text-slate-300 hover:text-rose-300 border border-white/5 hover:border-rose-500/30 transition">
                    <i class="fa-solid fa-arrow-right-from-bracket"></i>
                    <span>Logout</span>
                </button>
            </form>
        </div>
    </aside>

    <!-- Main Content Area -->
    <div class="ml-64 flex-1 flex flex-col min-h-screen">
        
        <!-- Glassmorphism Topbar -->
        <header class="h-16 border-b border-white/10 bg-slate-950/80 backdrop-blur-xl sticky top-0 z-30 px-8 flex items-center justify-between">
            <h1 class="text-lg font-bold text-white tracking-tight flex items-center gap-2">
                @yield('header_title', 'CargoTrace Management')
            </h1>
            <div class="flex items-center gap-4 text-xs">
                <span class="px-3 py-1 rounded-full bg-blue-500/10 text-blue-400 border border-blue-500/20 font-medium flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    Sistem Operasional Aktif
                </span>
            </div>
        </header>

        <!-- Flash Messages -->
        <div class="px-8 pt-6">
            @if(session('success'))
                <div class="p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-300 text-sm flex items-center gap-3">
                    <i class="fa-solid fa-circle-check text-lg"></i>
                    <span>{{ session('success') }}</span>
                </div>
            @endif
            @if(session('warning'))
                <div class="p-4 rounded-xl bg-amber-500/10 border border-amber-500/30 text-amber-300 text-sm flex items-center gap-3">
                    <i class="fa-solid fa-triangle-exclamation text-lg"></i>
                    <span>{{ session('warning') }}</span>
                </div>
            @endif
            @if($errors->any())
                <div class="p-4 rounded-xl bg-rose-500/10 border border-rose-500/30 text-rose-300 text-sm flex items-center gap-3">
                    <i class="fa-solid fa-circle-xmark text-lg"></i>
                    <span>{{ $errors->first() }}</span>
                </div>
            @endif
        </div>

        <!-- Page Body Content -->
        <main class="p-8 flex-1">
            @yield('content')
        </main>

        <!-- Footer -->
        <footer class="border-t border-white/5 py-4 px-8 text-xs text-slate-400 flex items-center justify-between">
            <span>&copy; 2026 CargoTrace — Global Shipment Visibility & Tracking Platform (UAS Web Programming 2)</span>
            <span>Laravel 8 &bull; MySQL &bull; Leaflet &bull; Open-Meteo API</span>
        </footer>

    </div>

    @yield('scripts')
</body>
</html>

