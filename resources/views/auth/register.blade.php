<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Akun — CargoTrace</title>

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap');
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .glass-panel {
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.08);
        }
    </style>
</head>
<body class="bg-slate-950 text-slate-100 min-h-screen flex items-center justify-center p-4 selection:bg-blue-600 selection:text-white">

    <div class="fixed top-10 left-10 w-96 h-96 bg-blue-600/10 rounded-full blur-3xl pointer-events-none"></div>
    <div class="fixed bottom-10 right-10 w-96 h-96 bg-cyan-500/10 rounded-full blur-3xl pointer-events-none"></div>

    <div class="max-w-md w-full">
        <div class="text-center mb-8">
            <a href="/" class="inline-flex items-center gap-3">
                <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-blue-600 to-cyan-500 flex items-center justify-center text-white shadow-xl shadow-blue-500/30">
                    <i class="fa-solid fa-truck-fast text-xl"></i>
                </div>
            </a>
            <h2 class="text-2xl font-black text-white tracking-tight mt-3">Cargo<span class="text-blue-500">Trace</span></h2>
            <p class="text-xs text-slate-400 mt-1">Buat akun pelanggan untuk memantau seluruh kargo Anda</p>
        </div>

        <div class="glass-panel p-8 rounded-3xl shadow-2xl">
            @if($errors->any())
                <div class="mb-5 p-3 rounded-xl bg-rose-500/10 border border-rose-500/30 text-rose-300 text-xs flex items-center gap-2">
                    <i class="fa-solid fa-circle-exclamation"></i>
                    <span>{{ $errors->first() }}</span>
                </div>
            @endif

            <form action="{{ route('register.post') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1.5">Nama Lengkap</label>
                    <input 
                        type="text" 
                        name="name" 
                        value="{{ old('name') }}" 
                        required
                        class="w-full bg-slate-900/60 border border-white/10 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition"
                    />
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1.5">Alamat Email</label>
                    <input 
                        type="email" 
                        name="email" 
                        value="{{ old('email') }}" 
                        required
                        class="w-full bg-slate-900/60 border border-white/10 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition"
                    />
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1.5">Nomor Telepon / WhatsApp</label>
                    <input 
                        type="text" 
                        name="phone" 
                        value="{{ old('phone') }}" 
                        placeholder="+62..."
                        class="w-full bg-slate-900/60 border border-white/10 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition"
                    />
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1.5">Kata Sandi (min. 8 karakter)</label>
                    <input 
                        type="password" 
                        name="password" 
                        required
                        class="w-full bg-slate-900/60 border border-white/10 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition"
                    />
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1.5">Konfirmasi Kata Sandi</label>
                    <input 
                        type="password" 
                        name="password_confirmation" 
                        required
                        class="w-full bg-slate-900/60 border border-white/10 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition"
                    />
                </div>

                <button type="submit" class="w-full mt-2 bg-blue-600 hover:bg-blue-500 text-white font-bold py-3 rounded-xl text-sm transition shadow-lg shadow-blue-600/30">
                    Daftar Sebagai Customer
                </button>
            </form>

            <div class="mt-6 text-center text-xs text-slate-400">
                Sudah memiliki akun? <a href="{{ route('login') }}" class="text-blue-400 hover:underline font-semibold">Masuk di sini</a>
            </div>
        </div>
    </div>

</body>
</html>

