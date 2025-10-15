<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builders\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_category_id',
        'supplier_id',
        'sku',
        'barcode',
        'name',
        'generic_name',
        'description',
        'unit_of_measure',
        'pack_size',
        'cost_price',
        'selling_price',
        'tax_rate',
        'reorder_level',
        'reorder_quantity',
        'track_batches',
        'track_serial_numbers',
        'expiry_required',
        'is_prescription_only',
        'is_controlled_substance',
        'is_active',
        'storage_instructions',
        'photo_path',
        'last_inventory_count_at',
        'low_stock_notified_at',
        'metadata',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'pack_size' => 'integer',
            'cost_price' => 'decimal:2',
            'selling_price' => 'decimal:2',
            'tax_rate' => 'decimal:2',
            'reorder_level' => 'integer',
            'reorder_quantity' => 'integer',
            'track_batches' => 'boolean',
            'track_serial_numbers' => 'boolean',
            'expiry_required' => 'boolean',
            'is_prescription_only' => 'boolean',
            'is_controlled_substance' => 'boolean',
            'is_active' => 'boolean',
            'last_inventory_count_at' => 'datetime',
            'low_stock_notified_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    public function category()
    {
        return $this->belongsTo(ProductCategory::class, 'product_category_id');
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function batches()
    {
        return $this->hasMany(InventoryBatch::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeOutOfStock(Builder $query): Builder
    {
        return $query->whereDoesntHave('batches', function (Builder $batchQuery) {
            $batchQuery->where('quantity_available', '>', 0)->where('status', 'available');
        });
    }
}
