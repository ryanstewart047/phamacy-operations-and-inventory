<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'alt_phone',
        'date_of_birth',
        'gender',
        'address',
        'city',
        'state',
        'country',
        'credit_limit',
        'outstanding_balance',
        'loyalty_points',
        'last_purchase_at',
        'preferred_contact_method',
        'is_active',
        'notes',
        'metadata',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
            'credit_limit' => 'decimal:2',
            'outstanding_balance' => 'decimal:2',
            'loyalty_points' => 'integer',
            'last_purchase_at' => 'datetime',
            'is_active' => 'boolean',
            'metadata' => 'array',
        ];
    }

    public function sales()
    {
        return $this->hasMany(Sale::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }
}
