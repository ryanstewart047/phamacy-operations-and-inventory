<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSaleRequest;
use App\Models\Customer;
use App\Models\InventoryBatch;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Sale;
use App\Services\InventoryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class SalesController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:sales.view')->only(['index', 'show']);
        $this->middleware('can:sales.create')->only(['create', 'store']);
    }

    public function index(Request $request): Response
    {
        $filters = $request->only(['search', 'status', 'payment_status', 'sale_type', 'from', 'to']);

        $baseQuery = Sale::query()
            ->with(['cashier:id,name', 'customer:id,name'])
            ->when($filters['search'] ?? null, function ($query, $search) {
                $query->where(function ($inner) use ($search) {
                    $inner->where('sale_number', 'like', "%{$search}%")
                        ->orWhere('customer_name', 'like', "%{$search}%")
                        ->orWhere('customer_phone', 'like', "%{$search}%");
                });
            })
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->when($filters['payment_status'] ?? null, fn ($query, $status) => $query->where('payment_status', $status))
            ->when($filters['sale_type'] ?? null, fn ($query, $type) => $query->where('sale_type', $type))
            ->when($filters['from'] ?? null, function ($query, $from) {
                $query->whereDate('sold_at', '>=', $from);
            })
            ->when($filters['to'] ?? null, function ($query, $to) {
                $query->whereDate('sold_at', '<=', $to);
            });

        $amountQuery = (clone $baseQuery);
        $countQuery = (clone $baseQuery);
        $profitQuery = (clone $baseQuery);

        $sales = $baseQuery
            ->orderByDesc('sold_at')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        $sales->getCollection()->transform(function (Sale $sale) {
            return [
                'id' => $sale->id,
                'sale_number' => $sale->sale_number,
                'customer' => [
                    'id' => $sale->customer?->id,
                    'name' => $sale->customer?->name ?? $sale->customer_name,
                    'phone' => $sale->customer_phone,
                ],
                'cashier' => $sale->cashier?->only(['id', 'name']),
                'total_amount' => (float) $sale->total_amount,
                'amount_paid' => (float) $sale->amount_paid,
                'balance_due' => (float) $sale->balance_due,
                'status' => $sale->status,
                'payment_status' => $sale->payment_status,
                'sale_type' => $sale->sale_type,
                'sold_at' => optional($sale->sold_at)->toDateTimeString(),
            ];
        });

        $stats = [
            'total_amount' => (float) $amountQuery->sum('total_amount'),
            'transactions' => (int) $countQuery->count(),
            'profit' => (float) $profitQuery->sum('profit_total'),
        ];

        return Inertia::render('Sales/Index', [
            'sales' => $sales,
            'filters' => $filters,
            'stats' => $stats,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Sales/Create', [
            'customers' => Customer::orderBy('name')->get(['id', 'name', 'phone']),
            'products' => Product::active()->orderBy('name')->get(['id', 'name', 'sku', 'barcode', 'selling_price']),
        ]);
    }

    public function store(StoreSaleRequest $request, InventoryService $inventoryService): RedirectResponse
    {
    $validated = $request->validated();
        $user = $request->user();

        $itemInputs = collect($validated['items']);
        $productIds = $itemInputs->pluck('product_id')->all();
        $batchIds = $itemInputs->pluck('inventory_batch_id')->filter()->all();

        $products = Product::whereIn('id', $productIds)->get()->keyBy('id');
        $batches = $batchIds ? InventoryBatch::whereIn('id', $batchIds)->get()->keyBy('id') : collect();

        $itemsData = [];
        $subtotal = 0;
        $discountTotal = 0;
        $taxTotal = 0;
        $totalAmount = 0;
        $costTotal = 0;

        // Aggregate totals while preparing sale items for persistence.
        foreach ($itemInputs as $itemInput) {
            $product = $products->get($itemInput['product_id']);

            if (! $product) {
                abort(422, __('One or more selected products could not be found.'));
            }

            $batch = null;

            if (! empty($itemInput['inventory_batch_id'])) {
                $batch = $batches->get($itemInput['inventory_batch_id']);
            }

            $quantity = (int) $itemInput['quantity'];
            $unitPrice = (float) $itemInput['unit_price'];
            $discount = (float) ($itemInput['discount_amount'] ?? 0);
            $tax = (float) ($itemInput['tax_amount'] ?? 0);

            $lineSubtotal = $quantity * $unitPrice;
            $lineTotal = $lineSubtotal - $discount + $tax;
            $unitCost = (float) ($batch?->unit_cost ?? $product->cost_price ?? 0);
            $lineCost = $unitCost * $quantity;
            $lineProfit = $lineTotal - $lineCost;

            $subtotal += $lineSubtotal;
            $discountTotal += $discount;
            $taxTotal += $tax;
            $totalAmount += $lineTotal;
            $costTotal += $lineCost;

            $itemsData[] = [
                'product_id' => $product->id,
                'inventory_batch_id' => $batch?->id,
                'quantity' => $quantity,
                'unit_price' => round($unitPrice, 2),
                'discount_amount' => round($discount, 2),
                'tax_amount' => round($tax, 2),
                'total_amount' => round($lineTotal, 2),
                'cost_price' => round($unitCost, 2),
                'profit' => round($lineProfit, 2),
                'metadata' => $itemInput['metadata'] ?? null,
            ];
        }

        $paymentsInput = collect($validated['payments'] ?? []);
        $paymentsTotal = (float) $paymentsInput->sum(fn ($payment) => $payment['amount'] ?? 0);

        $amountPaid = $paymentsInput->isNotEmpty()
            ? $paymentsTotal
            : (float) ($validated['amount_paid'] ?? 0);

        $changeDue = max(0, $amountPaid - $totalAmount);
        $balanceDue = max(0, $totalAmount - $amountPaid);
        $profitTotal = $totalAmount - $costTotal;

        $paymentStatus = $balanceDue <= 0 ? 'paid' : ($amountPaid > 0 ? 'partial' : 'unpaid');

        $customer = null;

        if (! empty($validated['customer_id'])) {
            $customer = Customer::find($validated['customer_id']);

            if ($customer) {
                $validated['customer_name'] = $validated['customer_name'] ?? $customer->name;
                $validated['customer_phone'] = $validated['customer_phone'] ?? $customer->phone;
            }
        }

        $primaryPaymentMethod = $validated['payment_method'] ?? $paymentsInput->first()['method'] ?? null;

        $sale = DB::transaction(function () use (
            $validated,
            $user,
            $itemsData,
            $totalAmount,
            $subtotal,
            $discountTotal,
            $taxTotal,
            $amountPaid,
            $changeDue,
            $balanceDue,
            $costTotal,
            $profitTotal,
            $paymentStatus,
            $paymentsInput,
            $inventoryService,
            $primaryPaymentMethod
        ) {
            $sale = Sale::create([
                'sale_number' => $this->generateSaleNumber(),
                'user_id' => $user?->id,
                'customer_id' => $validated['customer_id'] ?? null,
                'customer_name' => $validated['customer_name'] ?? null,
                'customer_phone' => $validated['customer_phone'] ?? null,
                'status' => 'completed',
                'payment_status' => $paymentStatus,
                'sale_type' => $validated['sale_type'] ?? 'pos',
                'subtotal' => round($subtotal, 2),
                'discount_total' => round($discountTotal, 2),
                'tax_total' => round($taxTotal, 2),
                'total_amount' => round($totalAmount, 2),
                'amount_paid' => round($amountPaid, 2),
                'change_due' => round($changeDue, 2),
                'balance_due' => round($balanceDue, 2),
                'cost_total' => round($costTotal, 2),
                'profit_total' => round($profitTotal, 2),
                'payment_method' => $primaryPaymentMethod,
                'notes' => $validated['notes'] ?? null,
                'receipt_note' => $validated['receipt_note'] ?? null,
                'payload' => $validated['payload'] ?? null,
                'created_from' => $validated['created_from'] ?? null,
                'device_id' => $validated['device_id'] ?? null,
                'sold_at' => now(),
            ]);

            $sale->items()->createMany($itemsData);

            if ($paymentsInput->isNotEmpty()) {
                $payments = $paymentsInput->map(function (array $payment) use ($sale, $user) {
                    return [
                        'sale_id' => $sale->id,
                        'customer_id' => $sale->customer_id,
                        'payment_number' => $this->generatePaymentNumber(),
                        'amount' => round((float) ($payment['amount'] ?? 0), 2),
                        'currency' => strtoupper($payment['currency'] ?? 'USD'),
                        'method' => $payment['method'],
                        'reference' => $payment['reference'] ?? null,
                        'status' => 'completed',
                        'paid_at' => now(),
                        'received_by' => $user?->id,
                        'metadata' => $payment['metadata'] ?? null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                })->all();

                Payment::insert($payments);
            }

            try {
                $inventoryService->adjustAfterSale(array_map(fn ($item) => [
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                ], $itemsData));
            } catch (\Throwable $exception) {
                Log::error('Failed to adjust inventory after sale', [
                    'sale_id' => $sale->id,
                    'message' => $exception->getMessage(),
                ]);
                throw $exception;
            }

            return $sale;
        });

        return redirect()->route('sales.show', $sale)->with('flash', [
            'type' => 'success',
            'message' => __('Sale :number recorded successfully.', ['number' => $sale->sale_number]),
        ]);
    }

    public function show(Sale $sale): Response
    {
        $sale->load([
            'cashier:id,name,email',
            'customer:id,name,phone,email',
            'items.product:id,name,sku,barcode',
            'items.batch:id,batch_number,expiry_date',
            'payments' => fn ($query) => $query->orderBy('paid_at'),
        ]);

        return Inertia::render('Sales/Show', [
            'sale' => [
                'id' => $sale->id,
                'sale_number' => $sale->sale_number,
                'status' => $sale->status,
                'payment_status' => $sale->payment_status,
                'sale_type' => $sale->sale_type,
                'subtotal' => (float) $sale->subtotal,
                'discount_total' => (float) $sale->discount_total,
                'tax_total' => (float) $sale->tax_total,
                'total_amount' => (float) $sale->total_amount,
                'amount_paid' => (float) $sale->amount_paid,
                'change_due' => (float) $sale->change_due,
                'balance_due' => (float) $sale->balance_due,
                'cost_total' => (float) $sale->cost_total,
                'profit_total' => (float) $sale->profit_total,
                'sold_at' => optional($sale->sold_at)->toDateTimeString(),
                'notes' => $sale->notes,
                'receipt_note' => $sale->receipt_note,
                'payload' => $sale->payload,
                'customer' => $sale->customer?->only(['id', 'name', 'phone', 'email']),
                'cashier' => $sale->cashier?->only(['id', 'name', 'email']),
            ],
            'items' => $sale->items->map(fn ($item) => [
                'id' => $item->id,
                'product' => $item->product?->only(['id', 'name', 'sku', 'barcode']),
                'batch' => $item->batch ? [
                    'id' => $item->batch->id,
                    'batch_number' => $item->batch->batch_number,
                    'expiry_date' => optional($item->batch->expiry_date)->toDateString(),
                ] : null,
                'quantity' => (int) $item->quantity,
                'unit_price' => (float) $item->unit_price,
                'discount_amount' => (float) $item->discount_amount,
                'tax_amount' => (float) $item->tax_amount,
                'total_amount' => (float) $item->total_amount,
                'cost_price' => (float) $item->cost_price,
                'profit' => (float) $item->profit,
                'metadata' => $item->metadata,
            ])->values(),
            'payments' => $sale->payments->map(fn ($payment) => [
                'id' => $payment->id,
                'payment_number' => $payment->payment_number,
                'amount' => (float) $payment->amount,
                'currency' => $payment->currency,
                'method' => $payment->method,
                'reference' => $payment->reference,
                'status' => $payment->status,
                'paid_at' => optional($payment->paid_at)->toDateTimeString(),
            ])->values(),
        ]);
    }

    protected function generateSaleNumber(): string
    {
        return 'SAL-'.Str::upper(Str::ulid());
    }

    protected function generatePaymentNumber(): string
    {
        return 'PAY-'.Str::upper(Str::ulid());
    }
}
