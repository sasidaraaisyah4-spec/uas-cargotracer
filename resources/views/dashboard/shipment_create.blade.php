@extends('layouts.dashboard')

@section('title', 'Buat Pengiriman Baru')
@section('header_title', 'Create New Shipment Booking')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="glass-panel p-8 rounded-3xl shadow-2xl">
        <h2 class="text-lg font-bold text-white mb-6 flex items-center gap-2">
            <i class="fa-solid fa-plus-circle text-blue-400"></i> Form Booking & Manifest Kargo
        </h2>

        <form action="{{ route('dashboard.shipments.store') }}" method="POST" class="space-y-6 text-xs">
            @csrf

            <!-- Section 1: Carrier & Hub Locations -->
            <div class="p-5 rounded-2xl bg-slate-900/50 border border-white/5 space-y-4">
                <span class="text-xs font-bold text-cyan-400 uppercase tracking-wider block">1. Rute & Mitra Ekspedisi</span>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-slate-300 font-semibold mb-1">Mitra Carrier</label>
                        <select name="carrier_id" class="w-full py-2.5 px-3 rounded-xl bg-slate-900 border border-white/10 text-white">
                            <option value="">Pilih Carrier</option>
                            @foreach($carriers as $c)
                                <option value="{{ $c->id }}" {{ old('carrier_id') == $c->id ? 'selected' : '' }}>{{ $c->name }} ({{ $c->code }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-slate-300 font-semibold mb-1">Lokasi Asal (Origin Hub) *</label>
                        <select name="origin_location_id" required class="w-full py-2.5 px-3 rounded-xl bg-slate-900 border border-white/10 text-white">
                            <option value="">Pilih Hub Asal</option>
                            @foreach($locations as $l)
                                <option value="{{ $l->id }}" {{ old('origin_location_id') == $l->id ? 'selected' : '' }}>{{ $l->name }} ({{ $l->city }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-slate-300 font-semibold mb-1">Lokasi Tujuan (Destination Hub) *</label>
                        <select name="destination_location_id" required class="w-full py-2.5 px-3 rounded-xl bg-slate-900 border border-white/10 text-white">
                            <option value="">Pilih Hub Tujuan</option>
                            @foreach($locations as $l)
                                <option value="{{ $l->id }}" {{ old('destination_location_id') == $l->id ? 'selected' : '' }}>{{ $l->name }} ({{ $l->city }})</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <!-- Section 2: Parties Information -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Sender -->
                <div class="p-5 rounded-2xl bg-slate-900/50 border border-white/5 space-y-3">
                    <span class="text-xs font-bold text-blue-400 uppercase tracking-wider block">2. Data Pengirim (Sender)</span>
                    <div>
                        <label class="block text-slate-300 mb-1">Nama Pengirim *</label>
                        <input type="text" name="sender_name" value="{{ old('sender_name', auth()->user()->name) }}" required class="w-full py-2 px-3 rounded-xl bg-slate-900 border border-white/10 text-white">
                    </div>
                    <div>
                        <label class="block text-slate-300 mb-1">No. Telepon *</label>
                        <input type="text" name="sender_phone" value="{{ old('sender_phone', auth()->user()->phone ?? '+6281200000001') }}" required class="w-full py-2 px-3 rounded-xl bg-slate-900 border border-white/10 text-white">
                    </div>
                    <div>
                        <label class="block text-slate-300 mb-1">Alamat Lengkap Pengirim *</label>
                        <textarea name="sender_address" rows="2" required class="w-full py-2 px-3 rounded-xl bg-slate-900 border border-white/10 text-white">{{ old('sender_address') }}</textarea>
                    </div>
                </div>

                <!-- Recipient -->
                <div class="p-5 rounded-2xl bg-slate-900/50 border border-white/5 space-y-3">
                    <span class="text-xs font-bold text-emerald-400 uppercase tracking-wider block">3. Data Penerima (Recipient)</span>
                    <div>
                        <label class="block text-slate-300 mb-1">Nama Penerima *</label>
                        <input type="text" name="recipient_name" value="{{ old('recipient_name') }}" required class="w-full py-2 px-3 rounded-xl bg-slate-900 border border-white/10 text-white">
                    </div>
                    <div>
                        <label class="block text-slate-300 mb-1">No. Telepon *</label>
                        <input type="text" name="recipient_phone" value="{{ old('recipient_phone') }}" required class="w-full py-2 px-3 rounded-xl bg-slate-900 border border-white/10 text-white">
                    </div>
                    <div>
                        <label class="block text-slate-300 mb-1">Alamat Lengkap Penerima *</label>
                        <textarea name="recipient_address" rows="2" required class="w-full py-2 px-3 rounded-xl bg-slate-900 border border-white/10 text-white">{{ old('recipient_address') }}</textarea>
                    </div>
                </div>
            </div>

            <!-- Section 3: Service & Cargo Details -->
            <div class="p-5 rounded-2xl bg-slate-900/50 border border-white/5 space-y-3">
                <span class="text-xs font-bold text-amber-400 uppercase tracking-wider block">4. Spesifikasi Layanan & Muatan</span>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-slate-300 mb-1">Jenis Layanan</label>
                        <select name="service_type" class="w-full py-2 px-3 rounded-xl bg-slate-900 border border-white/10 text-white">
                            <option value="regular">Regular</option>
                            <option value="express">Express</option>
                            <option value="cargo" selected>Cargo (Maritime/Air)</option>
                            <option value="same_day">Same Day</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-slate-300 mb-1">Kategori Kargo</label>
                        <input type="text" name="cargo_type" value="General Machinery Cargo" placeholder="contoh: Electronics, Textiles..." class="w-full py-2 px-3 rounded-xl bg-slate-900 border border-white/10 text-white">
                    </div>
                    <div>
                        <label class="block text-slate-300 mb-1">Total Biaya (IDR)</label>
                        <input type="number" step="1000" name="total_price" value="2500000" class="w-full py-2 px-3 rounded-xl bg-slate-900 border border-white/10 text-white">
                    </div>
                </div>
            </div>

            <!-- Section 4: Items Manifest -->
            <div class="p-5 rounded-2xl bg-slate-900/50 border border-white/5 space-y-3">
                <span class="text-xs font-bold text-indigo-400 uppercase tracking-wider block">5. Item Barang Pertama (Volume akan otomatis dihitung)</span>
                <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
                    <div class="md:col-span-2">
                        <label class="block text-slate-300 mb-1">Nama Barang *</label>
                        <input type="text" name="items[0][item_name]" value="Industrial Electric Motors" required class="w-full py-2 px-3 rounded-xl bg-slate-900 border border-white/10 text-white">
                    </div>
                    <div>
                        <label class="block text-slate-300 mb-1">Jumlah (Qty) *</label>
                        <input type="number" name="items[0][quantity]" value="5" min="1" required class="w-full py-2 px-3 rounded-xl bg-slate-900 border border-white/10 text-white">
                    </div>
                    <div>
                        <label class="block text-slate-300 mb-1">Berat per item (kg) *</label>
                        <input type="number" step="0.1" name="items[0][weight]" value="40" required class="w-full py-2 px-3 rounded-xl bg-slate-900 border border-white/10 text-white">
                    </div>
                    <div>
                        <label class="block text-slate-300 mb-1">Panjang (cm)</label>
                        <input type="number" step="0.1" name="items[0][length]" value="60" class="w-full py-2 px-3 rounded-xl bg-slate-900 border border-white/10 text-white">
                    </div>
                    <div>
                        <label class="block text-slate-300 mb-1">Lebar (cm)</label>
                        <input type="number" step="0.1" name="items[0][width]" value="40" class="w-full py-2 px-3 rounded-xl bg-slate-900 border border-white/10 text-white">
                    </div>
                    <div>
                        <label class="block text-slate-300 mb-1">Tinggi (cm)</label>
                        <input type="number" step="0.1" name="items[0][height]" value="50" class="w-full py-2 px-3 rounded-xl bg-slate-900 border border-white/10 text-white">
                    </div>
                    <div>
                        <label class="block text-slate-300 mb-1">Nilai Barang (IDR)</label>
                        <input type="number" step="1000" name="items[0][declared_value]" value="25000000" class="w-full py-2 px-3 rounded-xl bg-slate-900 border border-white/10 text-white">
                    </div>
                </div>
            </div>

            <!-- Submit Buttons -->
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-white/10">
                <a href="{{ route('dashboard.shipments') }}" class="px-5 py-2.5 rounded-xl bg-white/5 hover:bg-white/10 text-slate-300 transition">
                    Batal
                </a>
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-500 text-white font-bold transition shadow-lg shadow-blue-600/30">
                    Terbitkan Booking & Generate Resi
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

