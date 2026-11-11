# CargoTrace — Global Shipment Visibility & Tracking Platform

[![Laravel Version](https://img.shields.io/badge/Laravel-8.83-FF2D20?style=flat&logo=laravel)](https://laravel.com)
[![PHP Version](https://img.shields.io/badge/PHP-7.4%20%7C%208.x-777BB4?style=flat&logo=php)](https://php.net)
[![Database](https://img.shields.io/badge/MySQL-8.0-4479A1?style=flat&logo=mysql)](https://mysql.com)
[![Tests](https://img.shields.io/badge/PHPUnit-12%20Passed-brightgreen?style=flat&logo=checkmarx)](https://phpunit.de)
[![License](https://img.shields.io/badge/License-MIT-green.svg)](LICENSE)

> **Academic Portfolio Project:** Web Programming 2 — UAS Final Project Specification  
> **Architecture:** Service Layer Pattern &bull; Role-Based Access Control (RBAC) &bull; Leaflet.js Geolocation &bull; Open-Meteo Weather API &bull; REST API with Laravel Sanctum

---

## 1. Problem & Business Solution

### The Problem
- Critical supply chain and cargo tracking data is frequently scattered across disparate carrier portals.
- Customers lack end-to-end transparency, real-time geographical visibility, and predictable Estimated Time of Arrival (ETA).
- Operational anomalies (customs holds, weather disruptions, port congestions) are not caught proactively, resulting in misallocated resources and unresolved shipping exceptions.

### The CargoTrace Solution
- **Global Shipment Lifecycle:** Strict state machine managing shipments from booking to delivery (`CREATED -> BOOKED -> PICKED_UP -> IN_TRANSIT -> OUT_FOR_DELIVERY -> DELIVERED`).
- **Append-Only Chronological Tracking:** Historical audit-proof timeline validation ensuring event timestamps never retroactively conflict (`occurred_at >= last_event`).
- **Deterministic ETA & Delay Engine:** Automated mathematical delay calculation based on configurable thresholds (default 12 hours) with granular severity metrics (`LOW`, `MEDIUM`, `HIGH`).
- **Interactive Geospatial Visualization:** Real-time routing on **Leaflet.js + OpenStreetMap** connecting Origin &rarr; Checkpoints &rarr; Destination with dynamic status pins.
- **Real-Time Maritime Weather Integration:** Live temperature, wind speed, and squall alerts powered by **Open-Meteo API** at the cargo's current coordinates.
- **Exception Management:** Dedicated workflow for reporting, escalating, and resolving logistics bottlenecks (`OPEN -> INVESTIGATING -> RESOLVED`).

---

## 2. Technology Stack & External Services

| Layer | Technology | Details |
| :--- | :--- | :--- |
| **Backend Framework** | Laravel 8.83.29 (PHP 7.4 / 8.x) | Eloquent ORM, Service Layer, Sanctum, Form Requests |
| **Database** | MySQL 8.0+ | InnoDB, Foreign Key Constraints, Composite Indexes |
| **Frontend UI** | Blade + Tailwind CSS (CDN) | Modern Dark Glassmorphism, Bento Grid Layout |
| **Mapping Engine** | Leaflet.js + OpenStreetMap | Interactive Route Polylines, Custom Checkpoint Markers |
| **Data Analytics** | Chart.js | Cargo Status Distribution & Carrier Fleet Allocation |
| **External Weather API**| Open-Meteo REST API | Live Temperature, Wind Speed, Weather Conditions |
| **External Geocoding** | Nominatim OpenStreetMap | Latitude & Longitude Resolution |
| **Testing Suite** | PHPUnit 9.6 | Feature Tests for Auth, RBAC Authorization & Rules |

---

## 3. System Architecture & Service Layer Pattern

CargoTrace adheres to the **Thin Controller, Fat Service** paradigm to maintain high testability and clean separation of concerns:

```
Browser / REST Client
         ↓
  Routes + Middleware (RoleMiddleware, auth:sanctum)
         ↓
    Controllers (Api\ShipmentController, DashboardWebController)
         ↓
   Service Layer (Business Rules & Transactions)
   ├── ShipmentService.php  (State machine, CBM volume calculation, DB transactions)
   ├── EtaService.php       (Deterministic delay hours & severity calculation)
   ├── TrackingService.php  (Append-only chronological timeline enforcement)
   ├── ExceptionService.php (Lifecycle of operational bottlenecks & resolution)
   └── ExternalApiService.php (Open-Meteo Weather & Nominatim Geocoding)
         ↓
  Eloquent Models & MySQL Database
```

---

## 4. Role-Based Access Control (RBAC) & Demo Accounts

All credentials use the password: `password123`

| Role | Email | Permissions & Capabilities |
| :--- | :--- | :--- |
| **Administrator** | `admin@cargotrace.test` | Full system governance, all shipments, carrier & location management, audit logs, analytics. |
| **Logistics Operator** | `operator.jkt@cargotrace.test` | Create shipments, append tracking events, report exceptions, resolve disruptions. |
| **Customer (Domestic)** | `customer.siti@example.com` | View personal shipments, live map, ETA timeline, unread in-app notifications. |
| **Customer (Global)** | `customer.john@globaltrade.test` | View import maritime shipments (`CT-20261108-SGSUB999`) and weather exception logs. |

*Tip: The web login page features **1-Click Quick Demo Login** buttons for instant evaluator access without manual typing.*

---

## 5. Local Setup & Installation

### Prerequisites
- PHP 7.4 or PHP 8.x
- Composer 2.x
- MySQL Database Server (XAMPP / Laragon / Docker)

### Installation Steps
```bash
# 1. Clone or open repository
cd d:/cargotrace

# 2. Install dependencies
composer install

# 3. Environment configuration
cp .env.example .env
php artisan key:generate

# 4. Database configuration (.env)
# Ensure MySQL is running on 127.0.0.1:3306
# DB_DATABASE=cargotrace

# 5. Execute migrations and seed demo data
php artisan migrate:fresh --seed

# 6. Start local development server
php artisan serve
```
Open **`http://127.0.0.1:8000`** in your browser.

---

## 6. Running Automated Tests

CargoTrace includes comprehensive feature test coverage validating authentication, role authorization boundaries, state machine transitions, and deterministic ETA calculations.

```bash
./vendor/bin/phpunit tests/Feature
```

**Test Suite Coverage:**
- `AuthTest.php`: Valid login, invalid credentials rejection (401), unauthenticated protection, customer registration.
- `ShipmentAuthorizationTest.php`: Customer isolation (Customer A cannot access Customer B's cargo - 403 Forbidden), staff-only operational guards.
- `BusinessRulesTest.php`: Prevention of status modification on terminal states (`DELIVERED`), enforcement of chronological tracking order, and ETA threshold mathematical evaluations.

---

## 7. REST API Endpoints Specification

All API endpoints return standard JSON responses:

### Authentication
- `POST /api/register` — Register customer account
- `POST /api/login` — Authenticate and receive Sanctum Bearer Token
- `GET /api/user` — Authenticated profile (Requires Token)
- `POST /api/logout` — Revoke token

### Public Tracking
- `GET /api/track/{trackingNumber}` — Public tracking query with route and timeline

### Shipments
- `GET /api/shipments` — Paginated list with multi-filters (`status`, `carrier_id`, `is_delayed`, `search`)
- `POST /api/shipments` — Create shipment booking with nested items array
- `GET /api/shipments/{id}` — Shipment details (with user authorization check)
- `PATCH /api/shipments/{id}` — Update shipment status via State Machine
- `DELETE /api/shipments/{id}` — Admin-only deletion

### Tracking Events & Exceptions
- `GET /api/shipments/{id}/tracking` — Chronological tracking timeline
- `POST /api/shipments/{id}/tracking` — Append-only tracking event logging
- `GET /api/shipments/{id}/exceptions` — List shipment bottlenecks
- `POST /api/shipments/{id}/exceptions` — Report exception (Flags status to `EXCEPTION`)
- `PATCH /api/exceptions/{id}` — Resolve exception and resume voyage

### Reference Resources
- `GET /api/carriers` & `POST /api/carriers`
- `GET /api/locations` & `POST /api/locations`
- `GET /api/notifications` & `PATCH /api/notifications/{id}/read`

*(A ready-to-import Postman Collection is provided in `CargoTrace.postman_collection.json`).*

---

## 8. External API & Map Attribution

- **Tile Provider:** [OpenStreetMap](https://www.openstreetmap.org/copyright) contributors under ODbL.
- **Weather Services:** [Open-Meteo](https://open-meteo.com) under CC BY 4.0 (Free non-commercial academic weather forecasts).
- **Geocoding Services:** [Nominatim OpenStreetMap](https://nominatim.org) under OSM guidelines.

---

## 9. Known Limitations & Engineering Decisions

1. **Non-Microservice Monolith:** CargoTrace is purposefully engineered as a modular Laravel monolith to minimize operational complexity for academic evaluation while strictly upholding enterprise patterns via the Service Layer.
2. **Deterministic ETA Rules:** ETA predictions rely on deterministic scheduled arrival differences rather than non-deterministic blackbox Machine Learning models, ensuring 100% testable and reproducible results.
3. **Simulated Weather Delays:** Weather data is drawn in real time from Open-Meteo; while adverse weather automatically highlights severe warnings, actual voyage halts require operator verification before altering shipping status.
