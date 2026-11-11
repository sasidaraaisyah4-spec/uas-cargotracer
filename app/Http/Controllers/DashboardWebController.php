<?php

namespace App\Http\Controllers;

use App\Models\Carrier;
use App\Models\Location;
use App\Models\Notification;
use App\Models\Shipment;
use App\Models\ShipmentException;
use App\Services\EtaService;
use App\Services\ExceptionService;
use App\Services\ExternalApiService;
use App\Services\ShipmentService;
use App\Services\TrackingService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardWebController extends Controller
{
    protected ShipmentService $shipmentService;
    protected TrackingService $trackingService;
    protected ExceptionService $exceptionService;
    protected ExternalApiService $externalApiService;
    protected EtaService $etaService;

    public function __construct(
        ShipmentService $shipmentService,
        TrackingService $trackingService,
        ExceptionService $exceptionService,
        ExternalApiService $externalApiService,
        EtaService $etaService
    ) {
        $this->shipmentService = $shipmentService;
        $this->trackingService = $trackingService;
        $this->exceptionService = $exceptionService;
        $this->externalApiService = $externalApiService;
        $this->etaService = $etaService;
    }

    /**
     * Dashboard Overview with KPI Analytics & Chart.js data
     */
    public function index()
    {
        $user = Auth::user();

        $query = Shipment::query();
        if ($user->role === 'customer') {
            $query->where('user_id', $user->id);
        }

        $totalShipments = (clone $query)->count();
        $inTransitShipments = (clone $query)->where('status', 'in_transit')->count();
        $deliveredShipments = (clone $query)->where('status', 'delivered')->count();
        $delayedShipments = (clone $query)->where('is_delayed', true)->count();
        $openExceptions = ShipmentException::where('status', '!=', 'resolved')
            ->when($user->role === 'customer', function ($q) use ($user) {
                $q->whereHas('shipment', function ($sq) use ($user) {
                    $sq->where('user_id', $user->id);
                });
            })->count();

        // Chart.js Data 1: Status Distribution
        $statuses = ['created', 'picked_up', 'in_transit', 'out_for_delivery', 'exception', 'delivered', 'cancelled'];
        $statusCounts = [];
        foreach ($statuses as $st) {
            $statusCounts[] = (clone $query)->where('status', $st)->count();
        }

        // Chart.js Data 2: Shipments by Carrier
        $carriers = Carrier::withCount(['shipments' => function ($q) use ($user) {
            if ($user->role === 'customer') {
                $q->where('user_id', $user->id);
            }
        }])->get();

        $recentShipments = (clone $query)->with(['carrier', 'originLocation', 'destinationLocation'])
            ->latest()
            ->take(6)
            ->get();

        return view('dashboard.index', compact(
            'totalShipments',
            'inTransitShipments',
            'deliveredShipments',
            'delayedShipments',
            'openExceptions',
            'statuses',
            'statusCounts',
            'carriers',
            'recentShipments'
        ));
    }

    /**
     * Shipments List with Multi-filters, search, pagination
     */
    public function shipments(Request $request)
    {
        $user = Auth::user();
        $shipments = $this->shipmentService->getPaginatedShipments($user, $request->all(), 10);
        $carriers = Carrier::where('status', 'active')->get();
        $locations = Location::all();

        return view('dashboard.shipments', compact('shipments', 'carriers', 'locations'));
    }

    /**
     * Show shipment detail page with interactive Map & Timeline
     */
    public function showShipment($id)
    {
        $user = Auth::user();
        $shipment = Shipment::with([
            'carrier',
            'originLocation',
            'destinationLocation',
            'items',
            'trackingEvents' => function ($q) {
                $q->with('location', 'creator')->orderBy('occurred_at', 'desc');
            },
            'exceptions' => function ($q) {
                $q->with('reporter', 'resolver')->latest();
            }
        ])->findOrFail($id);

        if ($user->role === 'customer' && $shipment->user_id !== $user->id) {
            abort(403, 'Anda tidak berhak melihat data pengiriman ini.');
        }

        $mapData = $this->externalApiService->getMapData($shipment);
        $currLat = $mapData['current_position']['latitude'];
        $currLng = $mapData['current_position']['longitude'];
        $weatherData = $this->externalApiService->getCurrentWeather($currLat, $currLng);
        $locations = Location::all();

        return view('dashboard.shipment_detail', compact('shipment', 'mapData', 'weatherData', 'locations'));
    }

    /**
     * Show create shipment form
     */
    public function createShipmentForm()
    {
        $carriers = Carrier::where('status', 'active')->get();
        $locations = Location::all();

        return view('dashboard.shipment_create', compact('carriers', 'locations'));
    }

    /**
     * Handle store shipment
     */
    public function storeShipment(Request $request)
    {
        $validated = $request->validate([
            'carrier_id' => 'nullable|exists:carriers,id',
            'origin_location_id' => 'required|exists:locations,id',
            'destination_location_id' => 'required|different:origin_location_id|exists:locations,id',
            'sender_name' => 'required|string|max:255',
            'sender_phone' => 'required|string|max:30',
            'sender_address' => 'required|string',
            'recipient_name' => 'required|string|max:255',
            'recipient_phone' => 'required|string|max:30',
            'recipient_address' => 'required|string',
            'cargo_type' => 'nullable|string|max:100',
            'service_type' => 'required|in:regular,express,same_day,cargo,economy',
            'total_price' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.item_name' => 'required|string|max:255',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.weight' => 'required|numeric|min:0.01',
            'items.*.length' => 'nullable|numeric|min:0',
            'items.*.width' => 'nullable|numeric|min:0',
            'items.*.height' => 'nullable|numeric|min:0',
            'items.*.declared_value' => 'nullable|numeric|min:0',
        ]);

        try {
            $shipment = $this->shipmentService->createShipment(Auth::user(), $validated);
            return redirect()->route('dashboard.shipments.show', $shipment->id)
                ->with('success', "Shipment berhasil dibuat dengan No. Resi: {$shipment->tracking_number}");
        } catch (Exception $e) {
            return back()->withInput()->withErrors(['msg' => $e->getMessage()]);
        }
    }

    /**
     * Add tracking event from web dashboard
     */
    public function addTrackingEvent(Request $request, $id)
    {
        $user = Auth::user();
        if (!in_array($user->role, ['operator', 'admin'])) {
            abort(403);
        }

        $shipment = Shipment::findOrFail($id);

        $validated = $request->validate([
            'location_id' => 'nullable|exists:locations,id',
            'status' => 'nullable|string|in:created,booked,picked_up,in_transit,arrived_at_facility,departed_from_facility,out_for_delivery,delivered,exception,cancelled',
            'event_title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
        ]);

        try {
            $this->trackingService->addTrackingEvent($shipment, $user, $validated);
            return back()->with('success', 'Tracking event berhasil ditambahkan ke timeline kargo.');
        } catch (Exception $e) {
            return back()->withErrors(['msg' => $e->getMessage()]);
        }
    }

    /**
     * Report exception from web dashboard
     */
    public function reportException(Request $request, $id)
    {
        $user = Auth::user();
        if (!in_array($user->role, ['operator', 'admin'])) {
            abort(403);
        }

        $shipment = Shipment::findOrFail($id);

        $validated = $request->validate([
            'exception_type' => 'required|string|in:customs_hold,weather_delay,port_congestion,damaged,misrouted,documentation_issue,delay,failed_attempt,incorrect_address,other',
            'severity' => 'required|in:low,medium,high,critical',
            'reason' => 'required|string|max:1000',
        ]);

        try {
            $this->exceptionService->reportException($shipment, $user, $validated);
            return back()->with('warning', 'Kendala operasional dilaporkan. Status shipment telah diubah menjadi EXCEPTION.');
        } catch (Exception $e) {
            return back()->withErrors(['msg' => $e->getMessage()]);
        }
    }

    /**
     * Resolve exception from web dashboard
     */
    public function resolveException(Request $request, $id)
    {
        $user = Auth::user();
        if (!in_array($user->role, ['operator', 'admin'])) {
            abort(403);
        }

        $exception = ShipmentException::findOrFail($id);

        $validated = $request->validate([
            'status' => 'required|in:investigating,resolved',
            'resolution_notes' => 'required|string|max:1000',
        ]);

        try {
            $this->exceptionService->updateExceptionStatus($exception, $user, $validated['status'], $validated['resolution_notes']);
            return back()->with('success', 'Status kendala diperbarui. Kargo melanjutkan perjalanan.');
        } catch (Exception $e) {
            return back()->withErrors(['msg' => $e->getMessage()]);
        }
    }

    /**
     * In-App Notifications view
     */
    public function notifications()
    {
        $notifications = Auth::user()->notifications()->paginate(15);
        return view('dashboard.notifications', compact('notifications'));
    }

    public function markNotificationRead($id)
    {
        $notif = Notification::where('user_id', Auth::id())->findOrFail($id);
        $notif->markAsRead();
        return back()->with('success', 'Notifikasi ditandai telah dibaca.');
    }
}

