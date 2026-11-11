# CargoTrace — Engineering Skills & Coding Standards

Dokumen ini menetapkan standar implementasi teknis pada proyek **CargoTrace** agar memenuhi kriteria penilaian *Engineering Quality* (Slide 21) dan siap menghadapi *Code Ownership Interview* (Slide 19).

---

## 1. Standar Kode Laravel 8 & PHP

1. **Kompatibilitas Sintaks Laravel 8.75:**
   - Karena `composer.json` proyek menggunakan `"laravel/framework": "^8.75"` dan `"php": "^7.3|^8.0"`, gunakan sintaks yang sepenuhnya kompatibel (seperti deklarasi class migration standar atau anonymous migration yang didukung Laravel 8.37+, serta penggunaan `Illuminate\Support\Facades\Http` yang berbasis Guzzle 7).
2. **Strict Separation of Concerns:**
   - **Controller** hanya bertugas: mengambil input yang sudah tervalidasi dari `FormRequest` $\rightarrow$ memanggil method di `Service` $\rightarrow$ mengembalikan `JsonResponse` atau `View`.
   - **Service** bertugas: menjalankan aturan bisnis, membungkus operasi multi-tabel dengan `DB::transaction()`, memicu `AuditLog`, dan melempar `ValidationException` jika aturan bisnis dilanggar.
3. **Pencegahan Mass-Assignment & N+1 Query:**
   - Setiap model wajib mendefinisikan `$fillable` secara eksplisit.
   - Setiap query daftar (`index`) wajib menggunakan `with([...])` (*Eager Loading*) dan `paginate()` (*Server-Side Pagination*).

---

## 2. Standar Implementasi Business Rules

### A. State Machine Transisi Status Shipment
Aturan transisi status harus disimpan dalam konstanta atau konfigurasi terpusat di `Shipment` model / `ShipmentService`:
```php
public const ALLOWED_TRANSITIONS = [
    'CREATED'    => ['BOOKED', 'CANCELLED'],
    'BOOKED'     => ['PICKED_UP', 'CANCELLED'],
    'PICKED_UP'  => ['IN_TRANSIT'],
    'IN_TRANSIT' => ['OUT_FOR_DELIVERY', 'DELIVERED'],
    'OUT_FOR_DELIVERY' => ['DELIVERED', 'FAILED_DELIVERY'],
    'DELIVERED'  => [], // Terminal status: tidak boleh berpindah ke status lain
];
```
*(Catatan: Mendukung pemetaan case-insensitive agar tetap kompatibel dengan data seed lama jika diperlukan).*

### B. Validasi Urutan Waktu Tracking Event
Ketika Operator menambahkan `TrackingEvent` baru:
- Ambil event terakhir berdasarkan `occurred_at` terbesar pada shipment terkait.
- Jika `occurred_at` baru $<$ `occurred_at` terakhir, tolak request dengan HTTP `422 Unprocessable Entity` dan pesan error yang jelas.

### C. Perhitungan Deterministik ETA & Delay
- Rumus durasi keterlambatan (dalam jam):
  $$\text{Delay Hours} = \max\left(0, \frac{\text{ETA} - \text{Expected Arrival}}{3600}\right)$$
- Ambang batas (*threshold*) wajib diambil dari `config('cargotrace.delay_threshold_hours')` (yang membaca `.env`), sehingga saat dosen meminta perubahan threshold ketika *live interview*, Anda cukup mengubah satu nilai konfigurasi.

---

## 3. Standar UI/UX: Glassmorphism, Bento Grid & Sidebar

1. **Glassmorphism Styling (Tailwind CSS):**
   - Gunakan kombinasi latar belakang gelap berlapis gradien radial (`bg-slate-950`), efek kaca transparan (`bg-white/[0.04]` atau `bg-slate-900/60`), blur latar (`backdrop-blur-xl`), dan garis tepi tipis bercahaya (`border border-white/10 shadow-2xl`).
2. **Bento Grid Landing Page:**
   - Gunakan CSS Grid (`grid-cols-1 md:grid-cols-12 gap-4`) dengan variasi rentang kolom (`md:col-span-8`, `md:col-span-4`, `md:col-span-6`) untuk menampilkan informasi pelacakan, peta Leaflet, cuaca eksternal, dan statistik secara proporsional.
3. **Sidebar Dashboard (Admin & Staff):**
   - Sidebar tetap di kiri (`w-64`), menampilkan profil user beserta badge role (`ADMIN`, `OPERATOR`, `CUSTOMER`), indikator jumlah notifikasi belum dibaca, dan menu navigasi kontekstual sesuai otorisasi.