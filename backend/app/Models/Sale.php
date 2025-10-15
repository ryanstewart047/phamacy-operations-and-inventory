<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builders\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    use HasFactory;

    protected $fillable = [
        'sale_number',
        'user_id',
        'customer_id',
        'customer_name',
        'customer_phone',
        'status',
        'payment_status',
        'sale_type',
        'subtotal',
        'discount_total',
        'tax_total',
        'total_amount',
        'amount_paid',
        'change_due',
        'balance_due',
        'cost_total',
        'profit_total',
        'payment_method',
        'reference_number',
        'notes',
        'receipt_note',
        'payload',
        'created_from',
        'device_id',
        'sold_at',
        'voided_at',
        'synced_at',
        'voided_by',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'discount_total' => 'decimal:2',
            'tax_total' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'amount_paid' => 'decimal:2',
            'change_due' => 'decimal:2',
            'balance_due' => 'decimal:2',
            'cost_total' => 'decimal:2',
            'profit_total' => 'decimal:2',
            'payload' => 'array',
            'sold_at' => 'datetime',
            'voided_at' => 'datetime',
            'synced_at' => 'datetime',
        ];
    }

    public function items()
    {
        return $this->hasMany(SaleItem::class);
    }

    public function cashier()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function voider()
    {
        return $this->belongsTo(User::class, 'voided_by');
    }

    public function scopeToday(Builder $query): Builder
    {
        return $query->whereDate('sold_at', today());
    }

    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('status', 'completed');
    }
}
