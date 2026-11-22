<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StockMovement extends Model
{
    use HasFactory;

    protected $fillable = [
        'voucher_number',
        'type',
        'reference_type',
        'reference_id',
        'warehouse_manager_id',
        'voucher_date',
        'total_value',
        'received_or_delivered_by',
        'is_archived',
        'notes',
    ];

    protected $casts = [
        'voucher_date' => 'date',
        'total_value' => 'decimal:2',
        'is_archived' => 'boolean',
    ];

    public function warehouseManager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'warehouse_manager_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(StockMovementItem::class);
    }

    public function getTypeArabicAttribute(): string
    {
        return match ($this->type) {
            'receipt_supplier' => 'إذن استلام من مورد خارجي',
            'receipt_factory_internal' => 'إذن استلام من خط إنتاج داخلي (+14%)',
            'issue_production' => 'إذن صرف خامات للتشغيل',
            'delivery_customer' => 'إذن تسليم منتج تام لعميل',
            'inventory_adjustment' => 'تسوية جردية',
            'spare_part_maintenance' => 'إذن صرف قطع غيار للصيانة',
            default => $this->type,
        };
    }
}
