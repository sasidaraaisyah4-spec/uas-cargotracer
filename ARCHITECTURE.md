# CargoTrace — System Architecture Document

Dokumen ini mendefinisikan arsitektur teknis aplikasi **CargoTrace** berdasarkan stack yang terinstal pada proyek (`PHP ^7.3|^8.0`, `Laravel ^8.75`, `Sanctum ^2.11`, `MySQL 8+`) dan pedoman pada *Slide 14–15 CargoTrace UAS Final Project Brief*.

---

## 1. Diagram Arsitektur Berlapis (Layered Architecture)

Sesuai instruksi wajib pada *Slide 15*, aplikasi memisahkan penanganan HTTP (*Controllers*) dari logika bisnis (*Service Layer*):

```
┌─────────────────────────────────────────────────────────────────────────┐
│                        PRESENTATION & CLIENT LAYER                      │
│  • Public Landing Page (Hero Section + Bento Grid + Leaflet Map)        │
│  • Admin / Operator / Customer Glassmorphism Sidebar Dashboard (Blade)  │
│  • REST API Clients (Postman / Fetch API / Mobile / Third-Party)        │
└───────────────────────────────────┬─────────────────────────────────────┘
                                    │ HTTP / JSON Requests
                                    ▼
┌─────────────────────────────────────────────────────────────────────────┐
│                        ROUTING & MIDDLEWARE LAYER                       │
│  • routes/web.php (Session Auth, CSRF Protection, RoleMiddleware)       │
│  • routes/api.php (Sanctum Token Auth, Throttle, RoleMiddleware)        │
└───────────────────────────────────┬─────────────────────────────────────┘
                                    │
                                    ▼
┌─────────────────────────────────────────────────────────────────────────┐
│                     REQUEST VALIDATION & CONTROLLERS                    │
│  • Form Requests: StoreShipmentRequest, StoreTrackingRequest, etc.      │
│  • Web Controllers: TrackingWebController, DashboardController, etc.    │
│  • API Controllers: AuthController, ShipmentController,                 │
│    TrackingController, CarrierController, LocationController,           │
│    ExceptionController                                                  │
└───────────────────────────────────┬─────────────────────────────────────┘
                                    │ Calls Business Methods
                                    ▼
┌─────────────────────────────────────────────────────────────────────────┐
│                         BUSINESS SERVICE LAYER                          │
│  • ShipmentService   : CRUD, State Machine Transition, Audit Logging    │
│  • TrackingService   : Chronological Event Validation, Append-Only Log  │
│  • EtaService        : Deterministic Delay Calculation & Severity Rule  │
│  • ExceptionService  : Incident Lifecycle (OPEN/INVESTIGATING/RESOLVED) │
│  • ExternalApiService: Open-Meteo Weather / Nominatim Geocoding Client  │
└───────────────────────────────────┬─────────────────────────────────────┘
                                    │ Eloquent ORM & DB::transaction()
                                    ▼
┌─────────────────────────────────────────────────────────────────────────┐
│                      DATA PERSISTENCE LAYER (MySQL)                     │
│  users │ shipments │ shipment_items │ carriers │ locations              │
│  tracking_events │ shipment_exceptions │ notifications │ audit_logs     │
└─────────────────────────────────────────────────────────────────────────┘
```

---

## 2. Spesifikasi Detail Service Layer (`app/Services/`)

### 1. `ShipmentService`
- **Tanggung Jawab:**
  - Mengelola pembuatan shipment beserta `shipment_items`, event tracking awal, dan pencatatan `AuditLog` di dalam `DB::transaction()`.
  - Memvalidasi aturan transisi status (*State Machine*):
    - Transisi yang diizinkan: `CREATED` $\rightarrow$ `BOOKED` $\rightarrow$ `PICKED_UP` $\rightarrow$ `IN_TRANSIT` $\rightarrow$ `DELIVERED`.
    - Status terminal (`DELIVERED` / `CANCELLED`) dilarang kembali ke status awal.
  - Menyediakan query daftar shipment dengan *Eager Loading*, *Server-Side Filtering* (status, carrier, origin, destination, date range, delay status), *Sorting*, dan *Pagination*.

### 2. `TrackingService`
- **Tanggung Jawab:**
  - Menambahkan `TrackingEvent` baru secara historis (*append-only*, tidak pernah menimpa event lama).
  - Memvalidasi bahwa `occurred_at` pada event baru tidak lebih awal dari `occurred_at` event terakhir pada shipment tersebut.
  - Memperbarui lokasi terkini dan status shipment secara sinkron, serta mengirim notifikasi in-app ke pemilik shipment (`customer`).

### 3. `EtaService`
- **Tanggung Jawab:**
  - Membandingkan `estimated_delivery_at` (ETA) dengan `expected_arrival_at`.
  - Menghitung durasi keterlambatan dalam satuan jam (`delay_hours`).
  - Menentukan status keterlambatan (`is_delayed`) dan *severity* berdasarkan konfigurasi `config('cargotrace.delay_threshold')`:
    - $0 < \text{delay} \le 6$ jam: `LOW`
    - $6 < \text{delay} \le 24$ jam: `MEDIUM`
    - $\text{delay} > 24$ jam: `HIGH`

### 4. `ExceptionService`
- **Tanggung Jawab:**
  - Mencatat gangguan pengiriman (`CUSTOMS_HOLD`, `WEATHER_DELAY`, `PORT_CONGESTION`, `DAMAGED`, `MISROUTED`, `DOCUMENTATION_ISSUE`).
  - Mengelola perubahan status insiden (`OPEN` $\rightarrow$ `INVESTIGATING` $\rightarrow$ `RESOLVED`), menyimpan timestamp `detected_at` dan `resolved_at`, serta memicu pembuatan `Notification` dan `AuditLog`.

### 5. `ExternalApiService`
- **Tanggung Jawab:**
  - Menggunakan `Illuminate\Support\Facades\Http` dengan `timeout(5)` untuk memanggil layanan cuaca/geocoding pada koordinat lokasi shipment.
  - Menangani kegagalan koneksi, HTTP error ($4\text{xx}/5\text{xx}$), atau response kosong secara aman (*graceful degradation*) tanpa menghentikan proses bisnis utama.

---

## 3. Desain UI/UX Architecture

1. **Public Landing Page (`resources/views/welcome.blade.php`):**
   - **Hero Section:** Headline tegas dengan *glassmorphic search bar* pelacakan resi instan dan badge indikator sistem aktif.
   - **Bento Grid Layout:** Susunan kartu modular asimetris bergaya *Dark Glassmorphism* (`backdrop-blur-xl bg-slate-900/60 border border-white/10`) yang menampilkan:
     - Peta Interaktif **Leaflet.js** rute kargo.
     - Timeline perjalanan kronologis & indikator Exception.
     - Kartu metrik ETA & Kalkulasi Keterlambatan (*Delay Rule*).
     - Kartu Cuaca Lokasi Terkini (*External API Widget*).
     - Rincian Muatan (*Shipment Items*).
2. **Internal Application (`resources/views/layouts/dashboard.blade.php`):**
   - **Glassmorphism Sidebar Navigation:** Navigasi vertikal tetap di sisi kiri untuk Admin, Logistics Operator (Staff), dan Customer dengan pemisahan menu sesuai hak akses (*RBAC*).