<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SalesOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_number',
        'customer_id',
        'sales_rep_id',
        'status',
        'order_date',
        'target_delivery_date',
        'confirmed_delivery_date',
        'operations_lead_time_days',
        'procurement_lead_time_days',
        'subtotal',
        'tax',
        'discount',
        'total_amount',
        'total_estimated_cost',
        'profit_margin_percentage',
        'has_loss_alarm',
        'payment_status',
        'client_notes',
        'internal_notes',
    ];

    protected $casts = [
        'order_date' => 'date',
        'target_delivery_date' => 'date',
        'confirmed_delivery_date' => 'date',
        'subtotal' => 'decimal:2',
        'tax' => 'decimal:2',
        'discount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'total_estimated_cost' => 'decimal:2',
        'profit_margin_percentage' => 'decimal:2',
        'has_loss_alarm' => 'boolean',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function salesRep(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sales_rep_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(SalesOrderItem::class);
    }

    public function productionOrders(): HasMany
    {
        return $this->hasMany(ProductionOrder::class);
    }

    public function purchaseRequisitions(): HasMany
    {
        return $this->hasMany(PurchaseRequisition::class);
    }

    public function financialTransactions(): HasMany
    {
        return $this->hasMany(FinancialTransaction::class);
    }

    public function getStatusBadgeAttribute(): array
    {
        return match ($this->status) {
            'pending_operations' => ['label' => 'قيد فحص التشغيل', 'color' => 'bg-amber-100 text-amber-800 border-amber-300'],
            'operations_scheduled' => ['label' => 'تم تحديد مدة الإنتاج', 'color' => 'bg-blue-100 text-blue-800 border-blue-300'],
            'procurement_delayed' => ['label' => 'بانتظار رد المشتريات (نواقص)', 'color' => 'bg-purple-100 text-purple-800 border-purple-300'],
            'confirmed' => ['label' => 'معتمد / تم إبلاغ العميل', 'color' => 'bg-teal-100 text-teal-800 border-teal-300'],
            'in_production' => ['label' => 'جاري التصنيع بالخطوط', 'color' => 'bg-indigo-100 text-indigo-800 border-indigo-300'],
            'ready_in_warehouse' => ['label' => 'جاهز بالمخزن المركزي', 'color' => 'bg-emerald-100 text-emerald-800 border-emerald-300'],
            'completed' => ['label' => 'تم التسليم للعميل', 'color' => 'bg-green-100 text-green-800 border-green-300'],
            'cancelled' => ['label' => 'ملغي', 'color' => 'bg-rose-100 text-rose-800 border-rose-300'],
            default => ['label' => $this->status, 'color' => 'bg-gray-100 text-gray-800 border-gray-300'],
        };
    }
}
