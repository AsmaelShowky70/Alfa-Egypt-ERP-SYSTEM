<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Supplier extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'category',
        'opening_balance',
        'contact_person',
        'phone',
        'email',
        'address',
        'average_lead_time_days',
        'rating',
        'notes',
        'status',
    ];

    protected $casts = [
        'status' => 'boolean',
        'rating' => 'decimal:1',
        'opening_balance' => 'decimal:2',
    ];

    public function rawMaterials(): HasMany
    {
        return $this->hasMany(RawMaterial::class);
    }

    public function purchaseOrders(): HasMany
    {
        return $this->hasMany(PurchaseOrder::class);
    }

    public function financialTransactions(): HasMany
    {
        return $this->hasMany(FinancialTransaction::class);
    }

    public function getTotalBilledAttribute(): float
    {
        return (float) $this->financialTransactions()
            ->where('type', 'supplier_bill')
            ->sum('amount');
    }

    public function getTotalPaidAttribute(): float
    {
        return (float) $this->financialTransactions()
            ->where('type', 'supplier_payment')
            ->sum('amount');
    }

    public function getCurrentBalanceAttribute(): float
    {
        // Balance = opening_balance + billed - paid
        // Positive means Alfa Egypt owes supplier (دائن له مستحقات), Negative means advance credit paid to supplier (مدين / عربون مقدم)
        return (float) ($this->opening_balance + $this->total_billed - $this->total_paid);
    }
}
