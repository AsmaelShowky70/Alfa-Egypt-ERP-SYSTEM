<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PurchaseRequisition extends Model
{
    use HasFactory;

    protected $fillable = [
        'pr_number',
        'warehouse_manager_id',
        'sales_order_id',
        'production_order_id',
        'status',
        'procurement_lead_time_days',
        'estimated_arrival_date',
        'urgency',
        'reason',
        'procurement_reply_notes',
    ];

    protected $casts = [
        'estimated_arrival_date' => 'date',
    ];

    public function warehouseManager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'warehouse_manager_id');
    }

    public function salesOrder(): BelongsTo
    {
        return $this->belongsTo(SalesOrder::class);
    }

    public function productionOrder(): BelongsTo
    {
        return $this->belongsTo(ProductionOrder::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseRequisitionItem::class);
    }

    public function purchaseOrders(): HasMany
    {
        return $this->hasMany(PurchaseOrder::class);
    }
}
