<?php

namespace App\Http\Controllers;

use App\Http\Requests\PreviewOrderRequest;
use App\Http\Requests\StoreOrderRequest;
use App\Models\ActivityLog;
use App\Models\Client;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ProductVariant;
use App\Services\OrderFulfillmentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function __construct(
        protected OrderFulfillmentService $fulfillmentService
    ) {}

    /**
     * Display a listing of orders and clients.
     */
    public function index(Request $request): View
    {
        $search    = $request->get('search');
        $activeTab = $request->get('tab', 'orders');
        $dateFrom  = $request->get('date_from');
        $dateTo    = $request->get('date_to');

        $orders = Order::with(['createdBy', 'client', 'items'])
            ->where('is_archived', false)
            ->when($search, fn($q) => $q->where(fn($q2) => $q2
                ->where('po_number', 'like', "%{$search}%")
                ->orWhere('client_name', 'like', "%{$search}%")
                ->orWhere('status', 'like', "%{$search}%")))
            ->when($dateFrom, fn($q) => $q->whereDate('order_date', '>=', $dateFrom))
            ->when($dateTo,   fn($q) => $q->whereDate('order_date', '<=', $dateTo))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $archivedOrders  = Order::with(['createdBy', 'client', 'items'])
            ->where('is_archived', true)
            ->latest()
            ->get();
        $clients         = Client::where('is_archived', false)->orderBy('name')->get();
        $archivedClients = Client::where('is_archived', true)->orderBy('name')->get();
        $variants        = ProductVariant::orderBy('cheese_product')->orderBy('weight_grams')->get()->groupBy('cheese_product');

        return view('orders.index', compact(
            'orders',
            'archivedOrders',
            'clients',
            'archivedClients',
            'variants',
            'search',
            'activeTab',
            'dateFrom',
            'dateTo'
        ));
    }

    /**
     * Preview raw ingredient requirements for a prospective order.
     */
    public function preview(PreviewOrderRequest $request): JsonResponse
    {
        $result = $this->fulfillmentService->generatePreview($request->validated()['items']);

        return response()->json($result);
    }

    /**
     * Confirm an order immediately, deducting inventory atomically.
     */
    public function confirm(StoreOrderRequest $request): RedirectResponse
    {
        try {
            $validated = $request->validated();
            $order = $this->fulfillmentService->confirmOrder(
                $validated,
                $validated['items'],
                auth()->id()
            );

            return back()->with('success', "Order {$order->po_number} confirmed! Inventory has been updated.");
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->with('tab', 'orders');
        }
    }

    /**
     * Save a pending order without immediately deducting inventory.
     */
    public function store(StoreOrderRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $poNumber = 'PO-' . now()->format('Y') . '-' . strtoupper(substr(uniqid(), -5));

        $order = Order::create([
            'po_number'   => $poNumber,
            'client_id'   => $validated['client_id'] ?? null,
            'client_name' => $validated['client_name'],
            'order_date'  => $validated['order_date'],
            'quantity'    => 0,
            'unit'        => 'kg',
            'status'      => 'Pending',
            'notes'       => $validated['notes'] ?? null,
            'created_by'  => auth()->id(),
        ]);

        $totalKgAll = 0;
        foreach ($validated['items'] as $item) {
            $variant     = ProductVariant::findOrFail($item['variant_id']);
            $quantityPcs = (float) $item['quantity_pcs'];
            $totalKg     = ($variant->weight_grams / 1000) * $quantityPcs;
            $totalKgAll += $totalKg;

            OrderItem::create([
                'order_id'        => $order->id,
                'cheese_product'  => $item['product'],
                'variant_name'    => $variant->variant_name,
                'weight_grams'    => $variant->weight_grams,
                'quantity_pieces' => $quantityPcs,
                'total_kg'        => $totalKg,
            ]);
        }

        $order->update(['quantity' => $totalKgAll]);

        ActivityLog::record('Orders', 'Created Order', "Order {$poNumber} created for {$validated['client_name']}.");

        return back()->with('success', "Order {$poNumber} created successfully!");
    }

    /**
     * Update a pending order.
     */
    public function update(StoreOrderRequest $request, Order $order): RedirectResponse
    {
        if ($order->status !== 'Pending') {
            return back()->withErrors(['error' => 'Only pending orders can be edited.']);
        }

        $validated = $request->validated();

        $order->update([
            'client_id'   => $validated['client_id'] ?? null,
            'client_name' => $validated['client_name'],
            'order_date'  => $validated['order_date'],
            'notes'       => $validated['notes'] ?? null,
        ]);

        $order->items()->delete();

        $totalKgAll = 0;
        foreach ($validated['items'] as $item) {
            $variant     = ProductVariant::findOrFail($item['variant_id']);
            $quantityPcs = (float) $item['quantity_pcs'];
            $totalKg     = ($variant->weight_grams / 1000) * $quantityPcs;
            $totalKgAll += $totalKg;

            OrderItem::create([
                'order_id'        => $order->id,
                'cheese_product'  => $item['product'],
                'variant_name'    => $variant->variant_name,
                'weight_grams'    => $variant->weight_grams,
                'quantity_pieces' => $quantityPcs,
                'total_kg'        => $totalKg,
            ]);
        }

        $order->update(['quantity' => $totalKgAll]);

        ActivityLog::record('Orders', 'Updated Order', "Order {$order->po_number} updated.");

        return back()->with('success', "Order {$order->po_number} updated successfully!");
    }

    /**
     * Update order status with inventory synchronization.
     */
    public function updateStatus(Request $request, Order $order): RedirectResponse
    {
        $request->validate(['status' => 'required|in:Confirmed,Cancelled,Completed']);

        try {
            $this->fulfillmentService->updateStatus($order, $request->status, auth()->id());
            return back()->with('success', "Order {$order->po_number} has been {$request->status}.");
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors());
        }
    }

    /**
     * Archive an order.
     */
    public function archive(Order $order): RedirectResponse
    {
        if ($order->is_archived) {
            return back()->withErrors(['error' => 'Order is already archived.']);
        }

        if (!in_array($order->status, ['Completed', 'Cancelled'])) {
            return back()->withErrors(['error' => 'Only completed or cancelled orders can be archived.']);
        }

        $order->update(['is_archived' => true]);

        ActivityLog::record('Orders', 'Archived Order', "Order {$order->po_number} archived.");

        return back()->with('success', "Order {$order->po_number} has been archived.");
    }

    /**
     * Fetch product variants for AJAX selectors.
     */
    public function getVariants(Request $request): JsonResponse
    {
        $product = $request->get('product');
        $variants = ProductVariant::when($product, fn($q) => $q->where('cheese_product', $product))->get();

        return response()->json($variants);
    }
}
