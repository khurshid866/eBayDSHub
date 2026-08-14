<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreOrderRequest;
use App\Http\Requests\UpdateOrderRequest;
use App\Models\Company;
use App\Models\Order;
use App\Services\AuditLogService;
use App\Services\OrderCalculationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class OrderController extends Controller
{
    protected OrderCalculationService $calculator;
    protected AuditLogService $auditLogger;

    public function __construct(OrderCalculationService $calculator, AuditLogService $auditLogger)
    {
        $this->calculator = $calculator;
        $this->auditLogger = $auditLogger;
    }

    public function index(Request $request)
    {
        $this->authorize('viewAny', Order::class);

        $query = Order::query()->with(['creator', 'company']);

        if (!Auth::user()->isSuperAdmin()) {
            $query->where('company_id', Auth::user()->company_id);
        }

        // Search
        if ($request->filled('search')) {
            $query->search($request->input('search'));
        }

        // Filters
        if ($request->filled('status') && $request->input('status') !== 'all') {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('from_date')) {
            $query->whereDate('order_date', '>=', $request->input('from_date'));
        }

        if ($request->filled('to_date')) {
            $query->whereDate('order_date', '<=', $request->input('to_date'));
        }

        if ($request->filled('min_profit')) {
            $query->where('profit', '>=', (float)$request->input('min_profit'));
        }

        if ($request->filled('max_profit')) {
            $query->where('profit', '<=', (float)$request->input('max_profit'));
        }

        if ($request->filled('min_roi')) {
            $query->where('roi', '>=', (float)$request->input('min_roi') / 100);
        }

        if ($request->filled('max_roi')) {
            $query->where('roi', '<=', (float)$request->input('max_roi') / 100);
        }

        // Sorting
        $sortField = $request->input('sort', 'order_date');
        $sortDir = strtolower($request->input('direction', 'desc')) === 'asc' ? 'asc' : 'desc';

        $allowedSorts = ['order_date', 'ebay_order_number', 'customer_price', 'ad_fee_charges', 'amazon_order_number', 'supplier_cost', 'ebay_net', 'profit', 'roi', 'status', 'created_at'];

        if (in_array($sortField, $allowedSorts)) {
            $query->orderBy($sortField, $sortDir);
        } else {
            $query->orderBy('order_date', 'desc');
        }

        $perPage = (int) $request->input('per_page', 15);
        $orders = $query->paginate($perPage)->withQueryString();

        return view('orders.index', compact('orders'));
    }

    public function create()
    {
        $this->authorize('create', Order::class);
        return view('orders.create');
    }

    public function store(StoreOrderRequest $request)
    {
        $validated = $request->validated();
        $user = Auth::user();
        $companyId = $user->isSuperAdmin()
            ? (session('active_company_id') ?: ($user->company_id ?: Company::first()?->id))
            : $user->company_id;

        // Check duplicate eBay order number within company
        $existing = Order::where('company_id', $companyId)
            ->where('ebay_order_number', $validated['ebay_order_number'])
            ->first();

        if ($existing) {
            throw ValidationException::withMessages([
                'ebay_order_number' => "An order with eBay order number '{$validated['ebay_order_number']}' already exists in your company.",
            ]);
        }

        $custPrice = (float) $validated['customer_price'];
        $adFee = (float) ($validated['ad_fee_charges'] ?? 0);
        $supplierCost = (float) $validated['supplier_cost'];

        // Auto-calculate E_NET, Profit, and ROI
        $ebayNet = $this->calculator->calculateEbayNet($custPrice, $adFee);
        $profit = $this->calculator->calculateProfit($ebayNet, $supplierCost);
        $roi = $this->calculator->calculateRoi($profit, $ebayNet);

        $validated['company_id'] = $companyId;
        $validated['ad_fee_charges'] = $adFee;
        $validated['ebay_net'] = $ebayNet;
        $validated['profit'] = $profit;
        $validated['roi'] = $roi;
        $validated['created_by'] = $user->id;

        $order = Order::create($validated);

        $this->auditLogger->log('created_order', Order::class, $order->id, null, $order->toArray());

        return redirect()->route('orders.index')->with('success', "Order {$order->ebay_order_number} created successfully.");
    }

    public function show(Order $order)
    {
        $this->authorize('view', $order);
        $order->load(['creator', 'updater', 'company', 'auditLogs.user']);
        return view('orders.show', compact('order'));
    }

    public function edit(Order $order)
    {
        $this->authorize('update', $order);
        return view('orders.edit', compact('order'));
    }

    public function update(UpdateOrderRequest $request, Order $order)
    {
        $this->authorize('update', $order);

        $validated = $request->validated();
        $user = Auth::user();

        // Check duplicate eBay order number within company
        $existing = Order::where('company_id', $order->company_id)
            ->where('ebay_order_number', $validated['ebay_order_number'])
            ->where('id', '!=', $order->id)
            ->first();

        if ($existing) {
            throw ValidationException::withMessages([
                'ebay_order_number' => "An order with eBay order number '{$validated['ebay_order_number']}' already exists in your company.",
            ]);
        }

        $oldValues = $order->toArray();

        $custPrice = (float) $validated['customer_price'];
        $adFee = (float) ($validated['ad_fee_charges'] ?? 0);
        $supplierCost = (float) $validated['supplier_cost'];

        $ebayNet = $this->calculator->calculateEbayNet($custPrice, $adFee);
        $profit = $this->calculator->calculateProfit($ebayNet, $supplierCost);
        $roi = $this->calculator->calculateRoi($profit, $ebayNet);

        $validated['ad_fee_charges'] = $adFee;
        $validated['ebay_net'] = $ebayNet;
        $validated['profit'] = $profit;
        $validated['roi'] = $roi;
        $validated['updated_by'] = $user->id;

        // Never allow modifying company_id on update for non-superadmins
        if (!$user->isSuperAdmin()) {
            unset($validated['company_id']);
        }

        $order->update($validated);

        $this->auditLogger->log('updated_order', Order::class, $order->id, $oldValues, $order->toArray());

        return redirect()->route('orders.show', $order)->with('success', "Order {$order->ebay_order_number} updated successfully.");
    }

    public function destroy(Order $order)
    {
        $this->authorize('delete', $order);

        $oldValues = $order->toArray();
        $orderNumber = $order->ebay_order_number;

        $order->delete();

        $this->auditLogger->log('deleted_order', Order::class, $order->id, $oldValues, null);

        return redirect()->route('orders.index')->with('success', "Order {$orderNumber} soft deleted successfully.");
    }

    public function bulkAction(Request $request)
    {
        $this->authorize('viewAny', Order::class);

        $request->validate([
            'action' => ['required', 'string', 'in:delete,status_update'],
            'order_ids' => ['required', 'array'],
            'order_ids.*' => ['exists:orders,id'],
            'status' => ['nullable', 'string', 'in:Pending,Purchased,Shipped,Delivered,Completed,Cancelled,Refunded'],
        ]);

        $action = $request->input('action');
        $orderIds = $request->input('order_ids');
        $user = Auth::user();

        // Query orders strictly scoped by company for non-SuperAdmins
        $query = Order::whereIn('id', $orderIds);
        if (!$user->isSuperAdmin()) {
            $query->where('company_id', $user->company_id);
        }
        $orders = $query->get();

        if ($action === 'delete') {
            $deletedCount = 0;
            foreach ($orders as $order) {
                if ($user->can('delete', $order)) {
                    $order->delete();
                    $this->auditLogger->log('bulk_deleted_order', Order::class, $order->id);
                    $deletedCount++;
                }
            }
            return redirect()->route('orders.index')->with('success', $deletedCount . ' order(s) soft-deleted.');
        }

        if ($action === 'status_update') {
            $newStatus = $request->input('status');
            if ($newStatus) {
                $updatedCount = 0;
                foreach ($orders as $order) {
                    if ($user->can('update', $order)) {
                        $order->update(['status' => $newStatus, 'updated_by' => $user->id]);
                        $this->auditLogger->log('bulk_updated_status', Order::class, $order->id, null, ['status' => $newStatus]);
                        $updatedCount++;
                    }
                }
                return redirect()->route('orders.index')->with('success', $updatedCount . " order(s) updated to status '{$newStatus}'.");
            }
        }

        return redirect()->route('orders.index');
    }
}
