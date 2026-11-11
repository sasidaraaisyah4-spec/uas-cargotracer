# Product Requirements Document (PRD) — CargoTrace

**Nama Proyek:** CargoTrace — Global Shipment Visibility & Tracking Platform  
**Mata Kuliah:** Web Programming 2 (UAS Final Project)  
**Basis Framework:** Laravel 8.75, MySQL 8+, Laravel Sanctum, Tailwind CSS, Leaflet.js, Chart.js  

---

## 1. Visi Produk & Masalah yang Diselesaikan

CargoTrace menyelesaikan masalah fragmentasi informasi pengiriman kargo dengan menyediakan satu platform terpadu bagi **Administrator**, **Logistics Operator (Staff)**, dan **Customer** untuk:
1. Mengelola siklus hidup pengiriman (*Shipment Lifecycle*) dengan validasi *state machine* yang ketat di sisi server.
2. Menampilkan riwayat perjalanan (*Tracking Timeline*) dan posisi kargo pada peta interaktif (**Leaflet.js + OpenStreetMap**).
3. Mendeteksi keterlambatan (*ETA vs Expected Arrival*) secara deterministik dan mengelola insiden gangguan (*Exception Management*).
4. Menyajikan metrik operasional secara aktual melalui **Glassmorphism Sidebar Dashboard** dan **Bento Grid Landing Page**.

---

## 2. Peran Pengguna & Otorisasi Server-Side (RBAC)

| Role | Hak Akses & Fitur Utama |
| :--- | :--- |
| **Administrator (`admin`)** | • Akses penuh ke **Glassmorphism Sidebar Dashboard** & grafik analitik.<br>• Kelola data master **Carriers**, **Locations**, dan **Users**.<br>• Lihat seluruh **Shipments**, **Exceptions**, dan **Audit Logs** sistem.<br>• Ubah konfigurasi parameter keterlambatan (*delay threshold*). |
| **Logistics Operator / Staff (`operator`)** | • Akses ke **Sidebar Dashboard Operasional**.<br>• Buat dan perbarui **Shipments** serta rincian **Shipment Items**.<br>• Tambahkan **Tracking Events** baru & perbarui ETA.<br>• Laporkan dan kelola **Shipment Exceptions** (`OPEN` $\rightarrow$ `INVESTIGATING` $\rightarrow$ `RESOLVED`). |
| **Customer (`customer`)** | • Lacak pengiriman melalui **Landing Page (Hero + Bento Grid)** atau **Customer Dashboard**.<br>• Hanya dapat melihat daftar dan detail **Shipment miliknya sendiri**.<br>• Melihat rute peta, status ETA/delay, cuaca lokasi, dan **In-App Notifications**. |

---

## 3. Spesifikasi Fungsional Utama

### 3.1. Shipment Lifecycle & Status Transition
- Alur status standar yang divalidasi di `ShipmentService`:
  `CREATED` $\rightarrow$ `BOOKED` $\rightarrow$ `PICKED_UP` $\rightarrow$ `IN_TRANSIT` $\rightarrow$ `DELIVERED`.
- Mendukung kompatibilitas dengan status operasional (`pending`, `out_for_delivery`, `cancelled`).
- Status terminal (`DELIVERED`) dikunci agar tidak dapat dikembalikan ke status awal.
- Atribut wajib: `tracking_number`, `user_id` (customer), `carrier_id`, `origin_location_id`, `destination_location_id`, `cargo_type`, `total_weight`, `total_volume`, `expected_departure_at`, `expected_arrival_at`, `estimated_delivery_at` (ETA), `status`.

### 3.2. Historical Tracking Timeline
- Setiap event menyimpan: `shipment_id`, `location_id`, `created_by`, `status`, `event_title`, `description`, `latitude`, `longitude`, dan `occurred_at`.
- Bersifat historis (*append-only*): event baru tidak pernah menimpa event lama.
- Validasi kronologis: `occurred_at` event baru wajib $\ge$ event sebelumnya.

### 3.3. Deterministic ETA, Delay Rule & Exception Management
- **Aturan Delay:** Jika $\text{ETA} > \text{Expected Arrival}$, shipment ditandai sebagai **Delayed** beserta durasi keterlambatan dalam jam.
- **Severity Delay:** `LOW` ($0 - 6$ jam), `MEDIUM` ($> 6 - 24$ jam), `HIGH` ($> 24$ jam), atau menyesuaikan parameter threshold unik kelompok.
- **Exception Types:** `CUSTOMS_HOLD`, `WEATHER_DELAY`, `PORT_CONGESTION`, `DAMAGED`, `MISROUTED`, `DOCUMENTATION_ISSUE`.
- **Exception Status:** `OPEN` $\rightarrow$ `INVESTIGATING` $\rightarrow$ `RESOLVED` (mencatat `detected_at` dan `resolved_at`).

### 3.4. Peta Leaflet.js & Integrasi External API
- **Map:** Menampilkan marker Origin, Destination, dan titik-titik Tracking Event dengan garis rute (*polyline*) serta popup informasi.
- **External API:** Integrasi cuaca (*Open-Meteo API*) dan/atau *Nominatim Geocoding API* melalui Laravel HTTP Client dengan penanganan timeout dan fallback data.

### 3.5. Desain UI/UX (Bento Grid Landing Page & Glassmorphism Sidebar Dashboard)
- **Public Landing Page:** Menggunakan **Hero Section** modern dengan kotak pencarian resi instan, diikuti **Bento Grid** bergaya *Glassmorphism* yang menampilkan peta rute, cuaca real-time di lokasi kargo, status keterlambatan/ETA, timeline perjalanan, dan rincian muatan.
- **Admin & Staff Dashboard:** Menggunakan navigasi **Sidebar Glassmorphism** dengan kartu KPI, grafik **Chart.js** (*Shipment by Status*, *Shipment by Carrier*, *Delay & Exception Rate*), tabel dengan filter multi-kriteria & *server-side pagination*, panel **In-App Notifications**, serta **Audit Logs**.

---

## 4. Spesifikasi Endpoint REST API Wajib

| Kategori | Method & Endpoint | Akses Role |
| :--- | :--- | :--- |
| **Auth** | `POST /api/register`<br>`POST /api/login`<br>`POST /api/logout`<br>`GET /api/user` | Public<br>Public<br>Authenticated (Sanctum)<br>Authenticated (Sanctum) |
| **Shipments** | `GET /api/shipments`<br>`POST /api/shipments`<br>`GET /api/shipments/{id}`<br>`PUT/PATCH /api/shipments/{id}`<br>`DELETE /api/shipments/{id}` | Admin, Operator, Customer (Own)<br>Admin, Operator<br>Admin, Operator, Customer (Own)<br>Admin, Operator<br>Admin |
| **Tracking** | `GET /api/shipments/{id}/tracking`<br>`POST /api/shipments/{id}/tracking`<br>`GET /api/track/{trackingNumber}` | Admin, Operator, Customer (Own)<br>Admin, Operator<br>Public |
| **Resources & Exceptions** | `GET/POST /api/carriers`<br>`GET/POST /api/locations`<br>`GET/POST /api/shipments/{id}/exceptions`<br>`PATCH /api/exceptions/{id}` | Admin, Operator (POST Carrier/Loc: Admin)<br>Admin, Operator<br>Admin, Operator, Customer (GET Own)<br>Admin, Operator |