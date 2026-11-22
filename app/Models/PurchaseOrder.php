<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PurchaseOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'po_number',
        'purchase_requisition_id',
        'supplier_id',
        'is_internal_factory',
        'from_factory_id',
        'destination_warehouse',
        'internal_markup_percentage',
        'status',
        'order_date',
        'expected_delivery_date',
        'actual_delivery_date',
        'subtotal',
        'tax',
        'internal_markup_amount',
        'total_amount',
        'purchasing_manager_id',
        'notes',
    ];

    protected $casts = [
        'is_internal_factory' => 'boolean',
        'internal_markup_percentage' => 'decimal:2',
        'order_date' => 'date',
        'expected_delivery_date' => 'date',
        'actual_delivery_date' => 'date',
        'subtotal' => 'decimal:2',
        'tax' => 'decimal:2',
        'internal_markup_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
    ];

    public function purchaseRequisition(): BelongsTo
    {
        return $this->belongsTo(PurchaseRequisition::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function fromFactory(): BelongsTo
    {
        return $this->belongsTo(Factory::class, 'from_factory_id');
    }

    public function purchasingManager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'purchasing_manager_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    public function financialTransactions(): HasMany
    {
        return $this->hasMany(FinancialTransaction::class);
    }
}
