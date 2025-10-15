<?php

namespace App\Services;

use App\Models\InventoryBatch;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class InventoryService
{
    public function summarizeProductStock(Product $product): array
    {
        $batches = $product->batches()->select([
            'id',
            'quantity_available',
            'quantity_reserved',
            'status',
            'expiry_date',
        ])->get();

        $available = $batches->where('status', 'available')->sum('quantity_available');
        $reserved = $batches->sum('quantity_reserved');
        $expired = $batches->where('status', 'expired')->sum('quantity_available');

        return [
            'available' => (int) $available,
            'reserved' => (int) $reserved,
            'expired' => (int) $expired,
            'batches' => $batches,
        ];
    }

    public function totalOnHand(): int
    {
        return (int) InventoryBatch::where('status', 'available')->sum('quantity_available');
    }

    protected function outOfStockBaseQuery(): Builder
    {
        return Product::query()
            ->where('is_active', true)
            ->where(function (Builder $query) {
                $query->whereDoesntHave('batches', function (Builder $sub) {
                    $sub->where('status', 'available')
                        ->where('quantity_available', '>', 0);
                })->orWhere(function (Builder $sub) {
                    $sub->where('reorder_level', '>', 0)
                        ->whereRaw('(
                            select coalesce(sum(quantity_available), 0)
                            from inventory_batches
                            where inventory_batches.product_id = products.id
                            and inventory_batches.status = "available"
                        ) <= products.reorder_level');
                });
            });
    }

    public function outOfStockProducts(): Collection
    {
        return $this->outOfStockBaseQuery()
            ->with('supplier')
            ->orderBy('name')
            ->get();
    }

    public function countOutOfStockProducts(): int
    {
        return (int) $this->outOfStockBaseQuery()->count();
    }

    public function adjustAfterSale(array $items): void
    {
        DB::transaction(function () use ($items) {
            foreach ($items as $item) {
                /** @var Product $product */
                $product = Product::lockForUpdate()->findOrFail($item['product_id']);
                $remaining = (int) $item['quantity'];

                $batches = $product->batches()
                    ->where('status', 'available')
                    ->orderBy('expiry_date')
                    ->lockForUpdate()
                    ->get();

                foreach ($batches as $batch) {
                    if ($remaining <= 0) {
                        break;
                    }

                    $deduct = min($remaining, $batch->quantity_available);
                    $batch->quantity_available -= $deduct;
                    $remaining -= $deduct;

                    if ($batch->quantity_available <= 0) {
                        $batch->status = 'sold';
                    }

                    $batch->save();
                }
            }
        });
    }
}
