<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockMovement extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'inventory_batch_id',
        'type',
        'direction',
        'quantity',
        'unit_cost',
        'unit_price',
        'quantity_before',
        'quantity_after',
        'reference_type',
        'reference_id',
        'performed_by',
        'occurred_at',
        'notes',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'unit_cost' => 'decimal:2',
            'unit_price' => 'decimal:2',
            'quantity_before' => 'decimal:3',
            'quantity_after' => 'decimal:3',
            'occurred_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function batch()
    {
        return $this->belongsTo(InventoryBatch::class, 'inventory_batch_id');
    }

    public function performer()
    {
        return $this->belongsTo(User::class, 'performed_by');
    }

    public function reference()
    {
        return $this->morphTo();
    }
}
