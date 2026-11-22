<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseRequisitionItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_requisition_id',
        'raw_material_id',
        'needed_quantity',
        'current_stock_at_request',
        'supplier_id',
        'estimated_unit_price',
        'lead_time_days',
        'notes',
    ];

    protected $casts = [
        'needed_quantity' => 'decimal:2',
        'current_stock_at_request' => 'decimal:2',
        'estimated_unit_price' => 'decimal:4',
    ];

    public function purchaseRequisition(): BelongsTo
    {
        return $this->belongsTo(PurchaseRequisition::class);
    }

    public function rawMaterial(): BelongsTo
    {
        return $this->belongsTo(RawMaterial::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }
}
