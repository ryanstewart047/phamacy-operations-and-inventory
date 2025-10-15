<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseOrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_order_id',
        'product_id',
        'ordered_quantity',
        'received_quantity',
        'unit_cost',
        'tax_amount',
        'discount_amount',
        'total_cost',
        'batch_number',
        'expiry_date',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'ordered_quantity' => 'integer',
            'received_quantity' => 'integer',
            'unit_cost' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'total_cost' => 'decimal:2',
            'expiry_date' => 'date',
            'metadata' => 'array',
        ];
    }

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
