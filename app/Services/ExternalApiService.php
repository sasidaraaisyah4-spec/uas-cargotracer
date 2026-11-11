<?php

namespace App\Services;

use App\Models\Shipment;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ExternalApiService
{
    /**
     * Fetch real-time weather from Open-Meteo API
     *
     * @param float $latitude
     * @param float $longitude
     * @return array Weather metrics with graceful fallback
     */
    public function getCurrentWeather(float $latitude, float $longitude): array
    {
        $timeout = (int) config('cargotrace.http_timeout_seconds', 5);
        $apiUrl = config('cargotrace.weather_api_url', 'https://api.open-meteo.com/v1/forecast');

        try {
            $response = Http::timeout($timeout)->get($apiUrl, [
                'latitude' => $latitude,
                'longitude' => $longitude,
                'current_weather' => 'true',
                'hourly' => 'relativehumidity_2m',
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $current = $data['current_weather'] ?? [];

                $weatherCode = $current['weathercode'] ?? 0;
                $conditionInfo = $this->interpretWeatherCode((int) $weatherCode);

                return [
                    'status' => 'online',
                    'temperature' => $current['temperature'] ?? 28,
                    'windspeed' => $current['windspeed'] ?? 10,
                    'winddirection' => $current['winddirection'] ?? 0,
                    'weathercode' => $weatherCode,
                    'condition' => $conditionInfo['text'],
                    'icon' => $conditionInfo['icon'],
                    'is_adverse' => $conditionInfo['is_adverse'],
                ];
            }
        } catch (Exception $e) {
            Log::warning("Open-Meteo Weather API request failed: " . $e->getMessage());
        }

        // Graceful Fallback if service unavailable or offline
        return [
            'status' => 'offline',
            'temperature' => 28.5,
            'windspeed' => 12.0,
            'winddirection' => 90,
            'weathercode' => 0,
            'condition' => 'Weather Data Offline / Standard Nautical',
            'icon' => 'fa-cloud-sun',
            'is_adverse' => false,
        ];
    }

    /**
     * Geocode place name using Nominatim OpenStreetMap
     *
     * @param string $query
     * @return array|null [latitude, longitude, display_name]
     */
    public function geocodeLocation(string $query): ?array
    {
        $timeout = (int) config('cargotrace.http_timeout_seconds', 5);
        $apiUrl = config('cargotrace.geocoding_api_url', 'https://nominatim.openstreetmap.org/search');

        try {
            $response = Http::timeout($timeout)
                ->withHeaders([
                    'User-Agent' => 'CargoTrace-Platform/1.0 (academic; uas-webprogramming)',
                ])
                ->get($apiUrl, [
                    'q' => $query,
                    'format' => 'json',
                    'limit' => 1,
                ]);

            if ($response->successful() && !empty($response->json())) {
                $first = $response->json()[0];
                return [
                    'latitude' => (float) $first['lat'],
                    'longitude' => (float) $first['lon'],
                    'display_name' => $first['display_name'],
                ];
            }
        } catch (Exception $e) {
            Log::warning("Nominatim Geocoding API failed: " . $e->getMessage());
        }

        return null;
    }

    /**
     * Prepare structured map dataset for Leaflet.js rendering
     * Line: Origin -> Checkpoint Tracking Points -> Destination
     *
     * @param Shipment $shipment
     * @return array
     */
    public function getMapData(Shipment $shipment): array
    {
        $points = [];
        $polyline = [];

        // 1. Origin Marker
        $originLoc = $shipment->originLocation;
        $originLat = $originLoc && $originLoc->latitude ? (float) $originLoc->latitude : -6.1022;
        $originLng = $originLoc && $originLoc->longitude ? (float) $originLoc->longitude : 106.8825;

        $points[] = [
            'type' => 'origin',
            'title' => 'Asal: ' . ($originLoc ? $originLoc->name : $shipment->sender_name),
            'location' => $originLoc ? "{$originLoc->city}, {$originLoc->country_code}" : $shipment->sender_address,
            'latitude' => $originLat,
            'longitude' => $originLng,
            'color' => '#3b82f6', // blue
            'icon' => 'fa-circle-dot',
        ];
        $polyline[] = [$originLat, $originLng];

        // 2. Chronological Checkpoints / Tracking Events with coordinates
        $events = $shipment->trackingEvents()->orderBy('occurred_at', 'asc')->get();
        foreach ($events as $event) {
            $lat = $event->latitude ?? ($event->location ? $event->location->latitude : null);
            $lng = $event->longitude ?? ($event->location ? $event->location->longitude : null);

            if ($lat !== null && $lng !== null) {
                $points[] = [
                    'type' => 'checkpoint',
                    'title' => $event->event_title,
                    'location' => $event->description,
                    'time' => $event->occurred_at->format('d M Y, H:i') . ' WIB',
                    'status' => $event->status,
                    'latitude' => (float) $lat,
                    'longitude' => (float) $lng,
                    'color' => $event->status === 'exception' ? '#ef4444' : '#06b6d4', // cyan or rose
                    'icon' => $event->status === 'exception' ? 'fa-triangle-exclamation' : 'fa-location-dot',
                ];
                $polyline[] = [(float) $lat, (float) $lng];
            }
        }

        // 3. Destination Marker
        $destLoc = $shipment->destinationLocation;
        $destLat = $destLoc && $destLoc->latitude ? (float) $destLoc->latitude : -7.1985;
        $destLng = $destLoc && $destLoc->longitude ? (float) $destLoc->longitude : 112.7335;

        $points[] = [
            'type' => 'destination',
            'title' => 'Tujuan: ' . ($destLoc ? $destLoc->name : $shipment->recipient_name),
            'location' => $destLoc ? "{$destLoc->city}, {$destLoc->country_code}" : $shipment->recipient_address,
            'latitude' => $destLat,
            'longitude' => $destLng,
            'color' => '#10b981', // emerald
            'icon' => 'fa-flag-checkered',
        ];
        $polyline[] = [$destLat, $destLng];

        // Current location is the latest point in polyline before destination, or destination if delivered
        $currentCoord = end($polyline);
        if ($shipment->status !== 'delivered' && count($polyline) > 2) {
            $currentCoord = $polyline[count($polyline) - 2];
        }

        return [
            'points' => $points,
            'polyline' => $polyline,
            'current_position' => [
                'latitude' => $currentCoord[0] ?? $originLat,
                'longitude' => $currentCoord[1] ?? $originLng,
            ],
        ];
    }

    /**
     * Interpret WMO weather codes into readable format and icons
     */
    protected function interpretWeatherCode(int $code): array
    {
        switch ($code) {
            case 0:
                return ['text' => 'Clear Sky', 'icon' => 'fa-sun', 'is_adverse' => false];
            case 1:
            case 2:
            case 3:
                return ['text' => 'Partly Cloudy', 'icon' => 'fa-cloud-sun', 'is_adverse' => false];
            case 45:
            case 48:
                return ['text' => 'Foggy / Low Visibility', 'icon' => 'fa-smog', 'is_adverse' => true];
            case 51:
            case 53:
            case 55:
                return ['text' => 'Light Drizzle', 'icon' => 'fa-cloud-rain', 'is_adverse' => false];
            case 61:
            case 63:
            case 65:
                return ['text' => 'Moderate Rain', 'icon' => 'fa-cloud-showers-heavy', 'is_adverse' => true];
            case 80:
            case 81:
            case 82:
                return ['text' => 'Heavy Rain Showers', 'icon' => 'fa-cloud-showers-water', 'is_adverse' => true];
            case 95:
            case 96:
            case 99:
                return ['text' => 'Thunderstorm / Squall', 'icon' => 'fa-bolt-lightning', 'is_adverse' => true];
            default:
                return ['text' => 'Normal Maritime Conditions', 'icon' => 'fa-cloud', 'is_adverse' => false];
        }
    }
}

