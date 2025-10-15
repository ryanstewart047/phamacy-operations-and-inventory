<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Product;
use App\Models\Sale;
use App\Models\Supplier;
use App\Services\InventoryService;
use App\Services\ReportingService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __construct(
        private readonly InventoryService $inventoryService,
        private readonly ReportingService $reportingService,
    ) {
    }

    public function index(Request $request): Response
    {
        $summary = $this->reportingService->dailySalesSummary();

        $salesTrend = $this->reportingService
            ->salesTrend(14)
            ->map(fn ($item) => [
                'date' => $item->date,
                'total' => (float) $item->total,
                'profit' => (float) $item->profit,
            ]);

        $lowStock = $this->inventoryService
            ->outOfStockProducts()
            ->take(5)
            ->map(fn (Product $product) => [
                'id' => $product->id,
                'name' => $product->name,
                'sku' => $product->sku,
                'reorder_level' => $product->reorder_level,
                'supplier' => $product->supplier?->name,
            ]);

        $recentSales = Sale::with('customer')
            ->latest('sold_at')
            ->take(8)
            ->get()
            ->map(fn (Sale $sale) => [
                'id' => $sale->id,
                'sale_number' => $sale->sale_number,
                'customer' => $sale->customer?->name ?? $sale->customer_name,
                'total' => (float) $sale->total_amount,
                'profit' => (float) $sale->profit_total,
                'sold_at' => optional($sale->sold_at)->toDateTimeString(),
            ]);

        $inventoryOnHand = $this->inventoryService->totalOnHand();

        $meta = [
            'products' => Product::count(),
            'suppliers' => Supplier::count(),
            'customers' => Customer::count(),
            'inventory_on_hand' => $inventoryOnHand,
            'out_of_stock' => $lowStock->count(),
        ];

        return Inertia::render('Dashboard', [
            'summary' => $summary,
            'trend' => $salesTrend,
            'lowStock' => $lowStock,
            'recentSales' => $recentSales,
            'meta' => $meta,
        ]);
    }
}
