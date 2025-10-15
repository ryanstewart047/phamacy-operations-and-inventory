<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryBatch extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'batch_number',
        'manufactured_at',
        'expiry_date',
        'quantity_received',
        'quantity_available',
        'quantity_reserved',
        'unit_cost',
        'location',
        'status',
        'purchase_order_id',
        'notes',
        'metadata',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'manufactured_at' => 'date',
            'expiry_date' => 'date',
            'quantity_received' => 'integer',
            'quantity_available' => 'integer',
            'quantity_reserved' => 'integer',
            'unit_cost' => 'decimal:2',
            'metadata' => 'array',
        ];
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class);
    }
}
