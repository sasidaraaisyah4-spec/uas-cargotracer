<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Delay Calculation Thresholds
    |--------------------------------------------------------------------------
    |
    | Configuration for automated delay detection based on UAS Brief.
    | Default delay threshold: 12 hours (configurable via environment).
    |
    */
    'delay_threshold_hours' => (float) env('CARGOTRACE_DELAY_THRESHOLD_HOURS', 12.0),

    /*
    | Severity classification thresholds (in hours of delay)
    | 0 - 6 hours  => LOW
    | >6 - 24 hours => MEDIUM
    | >24 hours     => HIGH
    */
    'severity_thresholds' => [
        'low_max' => 6.0,
        'medium_max' => 24.0,
    ],

    /*
    |--------------------------------------------------------------------------
    | External API Services
    |--------------------------------------------------------------------------
    */
    'weather_api_url' => env('WEATHER_API_URL', 'https://api.open-meteo.com/v1/forecast'),
    'geocoding_api_url' => env('GEOCODING_API_URL', 'https://nominatim.openstreetmap.org/search'),
    'http_timeout_seconds' => (int) env('EXTERNAL_API_TIMEOUT', 5),

    /*
    |--------------------------------------------------------------------------
    | Shipment Lifecycle States
    |--------------------------------------------------------------------------
    */
    'statuses' => [
        'CREATED' => 'created',
        'BOOKED' => 'booked',
        'PICKED_UP' => 'picked_up',
        'IN_TRANSIT' => 'in_transit',
        'OUT_FOR_DELIVERY' => 'out_for_delivery',
        'EXCEPTION' => 'exception',
        'DELIVERED' => 'delivered',
        'CANCELLED' => 'cancelled',
        'PENDING' => 'pending', // backward compatibility alias for created
    ],

    /*
    | Valid forward status transitions
    */
    'allowed_transitions' => [
        'created' => ['booked', 'picked_up', 'exception', 'cancelled'],
        'pending' => ['booked', 'picked_up', 'exception', 'cancelled'],
        'booked' => ['picked_up', 'exception', 'cancelled'],
        'picked_up' => ['in_transit', 'exception', 'cancelled'],
        'in_transit' => ['out_for_delivery', 'exception', 'delivered', 'cancelled'],
        'out_for_delivery' => ['delivered', 'in_transit', 'exception', 'cancelled'],
        'exception' => ['in_transit', 'out_for_delivery', 'returned', 'cancelled'],
        'delivered' => [], // terminal state
        'cancelled' => [], // terminal state
    ],

    /*
    | Exception Types defined by UAS CargoTrace Specification
    */
    'exception_types' => [
        'customs_hold' => 'Customs Hold',
        'weather_delay' => 'Weather Delay',
        'port_congestion' => 'Port Congestion',
        'damaged' => 'Damaged Goods',
        'misrouted' => 'Misrouted Cargo',
        'documentation_issue' => 'Documentation Issue',
        'other' => 'Other Operational Issue',
    ],
];

