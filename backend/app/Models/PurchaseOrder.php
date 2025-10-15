<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builders\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'po_number',
        'supplier_id',
        'ordered_by',
        'received_by',
        'order_date',
        'expected_date',
        'received_date',
        'status',
        'payment_status',
        'subtotal',
        'tax_total',
        'discount_total',
        'freight_cost',
        'total_amount',
        'currency',
        'exchange_rate',
        'reference_number',
        'notes',
        'terms',
        'synced_at',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'order_date' => 'date',
            'expected_date' => 'date',
            'received_date' => 'date',
            'subtotal' => 'decimal:2',
            'tax_total' => 'decimal:2',
            'discount_total' => 'decimal:2',
            'freight_cost' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'exchange_rate' => 'decimal:6',
            'synced_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function items()
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    public function orderer()
    {
        return $this->belongsTo(User::class, 'ordered_by');
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    public function scopeOpen(Builder $query): Builder
    {
        return $query->whereIn('status', ['draft', 'ordered', 'partial']);
    }
}
