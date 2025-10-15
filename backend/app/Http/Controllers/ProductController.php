<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Supplier;
use App\Services\InventoryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class ProductController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:products.view')->only(['index', 'show']);
        $this->middleware('can:products.create')->only(['create', 'store']);
        $this->middleware('can:products.update')->only(['edit', 'update']);
        $this->middleware('can:products.delete')->only('destroy');
    }

    public function index(Request $request, InventoryService $inventoryService): Response
    {
        $filters = $request->only('search', 'status', 'category');

        $productsQuery = Product::query()
            ->with(['category:id,name', 'supplier:id,name'])
            ->when($filters['search'] ?? null, function ($query, $search) {
                $query->where(function ($inner) use ($search) {
                    $inner->where('name', 'like', "%{$search}%")
                        ->orWhere('sku', 'like', "%{$search}%")
                        ->orWhere('barcode', 'like', "%{$search}%");
                });
            })
            ->when($filters['status'] ?? null, function ($query, $status) {
                if ($status === 'active') {
                    $query->where('is_active', true);
                }

                if ($status === 'inactive') {
                    $query->where('is_active', false);
                }
            })
            ->when($filters['category'] ?? null, fn ($query, $category) => $query->where('product_category_id', $category))
            ->orderBy('name');

        $products = $productsQuery->paginate(15)->withQueryString();

        // Enrich the paginated collection with stock snapshots before returning to Inertia.
        $products->getCollection()->transform(function (Product $product) use ($inventoryService) {
            $stock = $inventoryService->summarizeProductStock($product);

            return [
                'id' => $product->id,
                'name' => $product->name,
                'sku' => $product->sku,
                'barcode' => $product->barcode,
                'category' => $product->category?->only(['id', 'name']),
                'supplier' => $product->supplier?->only(['id', 'name']),
                'selling_price' => (float) $product->selling_price,
                'cost_price' => (float) $product->cost_price,
                'reorder_level' => $product->reorder_level,
                'is_active' => (bool) $product->is_active,
                'stock' => [
                    'available' => $stock['available'],
                    'reserved' => $stock['reserved'],
                    'expired' => $stock['expired'],
                ],
            ];
        });

        $stats = [
            'total' => Product::count(),
            'active' => Product::where('is_active', true)->count(),
            'inactive' => Product::where('is_active', false)->count(),
            'out_of_stock' => $inventoryService->countOutOfStockProducts(),
        ];

        return Inertia::render('Inventory/Index', [
            'products' => $products,
            'filters' => $filters,
            'categories' => ProductCategory::orderBy('name')->get(['id', 'name']),
            'suppliers' => Supplier::orderBy('name')->get(['id', 'name']),
            'stats' => $stats,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Inventory/Create', [
            'categories' => ProductCategory::orderBy('name')->get(['id', 'name']),
            'suppliers' => Supplier::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(StoreProductRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['created_by'] = Auth::id();
        $data['updated_by'] = Auth::id();

        $product = Product::create($data);

        return redirect()->route('products.edit', $product)->with('flash', [
            'type' => 'success',
            'message' => __('Product :name created successfully.', ['name' => $product->name]),
        ]);
    }

    public function show(Product $product, InventoryService $inventoryService): Response
    {
        $product->load(['category:id,name', 'supplier:id,name']);

        $stock = $inventoryService->summarizeProductStock($product);

        return Inertia::render('Inventory/Show', [
            'product' => array_merge($product->only([
                'id',
                'name',
                'sku',
                'barcode',
                'description',
                'reorder_level',
                'reorder_quantity',
                'track_batches',
                'is_active',
            ]), [
                'selling_price' => (float) $product->selling_price,
                'cost_price' => (float) $product->cost_price,
                'category' => $product->category?->only(['id', 'name']),
                'supplier' => $product->supplier?->only(['id', 'name']),
            ]),
            'stock' => [
                'available' => $stock['available'],
                'reserved' => $stock['reserved'],
                'expired' => $stock['expired'],
            ],
            'batches' => $stock['batches']->map(fn ($batch) => [
                'id' => $batch->id,
                'quantity_available' => (int) $batch->quantity_available,
                'quantity_reserved' => (int) $batch->quantity_reserved,
                'status' => $batch->status,
                'expiry_date' => optional($batch->expiry_date)->toDateString(),
            ])->values(),
        ]);
    }

    public function edit(Product $product): Response
    {
        return Inertia::render('Inventory/Edit', [
            'product' => $product->load(['category:id,name', 'supplier:id,name']),
            'categories' => ProductCategory::orderBy('name')->get(['id', 'name']),
            'suppliers' => Supplier::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function update(UpdateProductRequest $request, Product $product): RedirectResponse
    {
        $data = $request->validated();
        $data['updated_by'] = Auth::id();

        $product->update($data);

        return redirect()->route('products.edit', $product)->with('flash', [
            'type' => 'success',
            'message' => __('Product updated successfully.'),
        ]);
    }

    public function destroy(Product $product): RedirectResponse
    {
        $product->delete();

        return redirect()->route('products.index')->with('flash', [
            'type' => 'success',
            'message' => __('Product deleted successfully.'),
        ]);
    }
}
