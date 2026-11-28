<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ReceivingInspection extends Model
{
    use HasFactory;

    protected $fillable = [
        'inspection_number',
        'inspection_date',
        'source_type',
        'supplier_id',
        'factory_id',
        'purchase_order_id',
        'supplier_invoice_number',
        'delivered_by',
        'inspector_name',
        'quality_member_name',
        'overall_status',
        'add_to_stock',
        'stock_movement_id',
        'warehouse_manager_id',
        'notes',
    ];

    protected $casts = [
        'inspection_date' => 'date',
        'add_to_stock' => 'boolean',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(ReceivingInspectionItem::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function factory(): BelongsTo
    {
        return $this->belongsTo(Factory::class);
    }

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function stockMovement(): BelongsTo
    {
        return $this->belongsTo(StockMovement::class);
    }

    public function warehouseManager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'warehouse_manager_id');
    }

    public function getStatusArabicAttribute(): string
    {
        return match ($this->overall_status) {
            'accepted' => 'مقبول ومطابق للمواصفات',
            'partial' => 'مقبول جزئياً (يوجد مرفوض)',
            'rejected' => 'مرفوض بالكامل',
            default => $this->overall_status,
        };
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->overall_status) {
            'accepted' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
            'partial' => 'bg-amber-100 text-amber-800 border-amber-200',
            'rejected' => 'bg-rose-100 text-rose-800 border-rose-200',
            default => 'bg-slate-100 text-slate-700 border-slate-200',
        };
    }

    public function getSourceDisplayNameAttribute(): string
    {
        if ($this->source_type === 'supplier' && $this->supplier) {
            return 'مورد: '.$this->supplier->name;
        }
        if ($this->source_type === 'internal_factory' && $this->factory) {
            return 'مصنع داخلي: '.$this->factory->name;
        }

        return $this->delivered_by ?: 'توريد مباشر';
    }
}
