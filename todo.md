# CARGOTRACE PROGRESS TRACKER (UAS WEB PROGRAMMING 2)

## FASE 1: Database Schema, Eloquent Models, Group Config & RBAC Middleware (P8–P9)
- [x] Inisialisasi `todo.md`
- [x] Buat `config/cargotrace.php` & update `.env.example`
- [x] Perbarui migration `shipments` (kolom UAS: cargo_type, total_volume, expected_departure_at, expected_arrival_at, is_delayed, delay_hours, delay_severity, status enum)
- [x] Perbarui migration `tracking_events` (kolom latitude, longitude)
- [x] Perbarui migration `shipment_exceptions` (kolom detected_at & enum UAS)
- [x] Buat migration `notifications`
- [x] Perbarui Model di `app/Models/` (Shipment, TrackingEvent, ShipmentException, User, Carrier, Location) & buat `Notification.php`
- [x] Buat `App\Http\Middleware\RoleMiddleware` & daftarkan alias di `Kernel.php`
- [x] Perbarui `CargoTraceSeeder.php` dengan dataset skenario UAS
- [x] Buat database `cargotrace`, jalankan `php artisan migrate:fresh --seed` & verifikasi record

## FASE 2: Service Layer & Business Rules Engine (P10–P11)
- [x] EtaService.php (Kalkulasi deterministik ETA, delay_hours, threshold, severity)
- [x] ShipmentService.php (State Machine transisi status, kalkulasi volume CBM, pagination/multi-filter, DB::transaction, AuditLog)
- [x] TrackingService.php (Penambahan append-only, validasi urutan waktu kronologis >= last_event, notifikasi)
- [x] ExceptionService.php (Pelaporan exception status khusus, eskalasi OPEN->INVESTIGATING->RESOLVED, resume status, audit)

## FASE 3: REST API Endpoints, Form Requests & Sanctum Authorization (P11–P12)
- [x] Form Requests (CreateShipmentRequest, UpdateShipmentRequest, AddTrackingEventRequest, ReportExceptionRequest, UpdateExceptionRequest)
- [x] API Controllers (Api/ShipmentController, Api/TrackingController, Api/CarrierController, Api/LocationController, Api/ExceptionController, Api/NotificationController, Api/AuthController)
- [x] Update `routes/api.php` (Semua endpoint wajib modul UAS + alias backward compatible)

## FASE 4: External API Integration & Map Data Service (P12)
- [x] ExternalApiService.php (Open-Meteo Weather API + Graceful Offline Fallback)
- [x] Geocoding integration (Nominatim OpenStreetMap)
- [x] Map dataset builder (Line: Origin -> Checkpoints -> Destination Polyline + Markers & Popup data)

## FASE 5: Web UI — Bento Grid Landing Page & Glassmorphism Sidebar Dashboard (P13)
- [x] Web Authentication (WebAuthController, Login & Register UI, 1-Click Quick Demo Login: Admin, Operator, Customer)
- [x] Bento Grid Landing Page (`welcome.blade.php`) + Leaflet.js Interactive Map + Open-Meteo Weather Widget + 12-Column Responsive Layout
- [x] Glassmorphism Sidebar Dashboard Layout (`layouts/dashboard.blade.php`)
- [x] Dashboard Analytics (`dashboard/index.blade.php`) + Chart.js (Status Distribution & Carrier Allocation)
- [x] Operational Views (Shipments List Multi-Filter & Pagination, Shipment Detail Map + Timeline, Create Shipment Form, Report/Resolve Exception, Notifications)

## FASE 6: Security Hardening, Automated Tests (PHPUnit) & Final Documentation (P14–P15)
- [x] Feature Tests (AuthTest, ShipmentAuthorizationTest, BusinessRulesTest) — 12/12 Passed (100% OK)
- [x] Postman Collection (`CargoTrace.postman_collection.json`)
- [x] English Professional Portfolio README.md (Architecture, Tech Stack, RBAC, Setup, Tests, API Docs, Attribution)

