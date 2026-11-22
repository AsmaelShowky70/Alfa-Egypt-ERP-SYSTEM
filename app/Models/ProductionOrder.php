<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductionOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_number',
        'sales_order_id',
        'factory_id',
        'product_id',
        'machine_id',
        'planned_quantity',
        'produced_quantity',
        'defective_quantity',
        'scheduled_start_date',
        'scheduled_end_date',
        'actual_start_date',
        'actual_end_date',
        'estimated_hours',
        'actual_hours',
        'status',
        'operations_manager_id',
        'instructions',
    ];

    protected $casts = [
        'planned_quantity' => 'decimal:2',
        'produced_quantity' => 'decimal:2',
        'defective_quantity' => 'decimal:2',
        'scheduled_start_date' => 'datetime',
        'scheduled_end_date' => 'datetime',
        'actual_start_date' => 'datetime',
        'actual_end_date' => 'datetime',
        'estimated_hours' => 'decimal:2',
        'actual_hours' => 'decimal:2',
    ];

    public function salesOrder(): BelongsTo
    {
        return $this->belongsTo(SalesOrder::class);
    }

    public function factory(): BelongsTo
    {
        return $this->belongsTo(Factory::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function machine(): BelongsTo
    {
        return $this->belongsTo(Machine::class);
    }

    public function operationsManager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'operations_manager_id');
    }

    public function materialRequisitions(): HasMany
    {
        return $this->hasMany(MaterialRequisition::class);
    }

    public function hourlyReports(): HasMany
    {
        return $this->hasMany(HourlyMachineReport::class);
    }

    public function getStatusBadgeAttribute(): array
    {
        return match ($this->status) {
            'draft' => ['label' => 'مسودة / تخطيط', 'color' => 'bg-gray-100 text-gray-800'],
            'waiting_materials' => ['label' => 'بانتظار صرف الخامات', 'color' => 'bg-amber-100 text-amber-800'],
            'ready' => ['label' => 'جاهز للبدء فوراً', 'color' => 'bg-blue-100 text-blue-800'],
            'in_progress' => ['label' => 'قيد التشغيل على الخط', 'color' => 'bg-indigo-100 text-indigo-800'],
            'completed' => ['label' => 'مكتمل وتم التوريد للمخزن', 'color' => 'bg-emerald-100 text-emerald-800'],
            'paused' => ['label' => 'متوقف مؤقتاً', 'color' => 'bg-orange-100 text-orange-800'],
            'cancelled' => ['label' => 'ملغي', 'color' => 'bg-rose-100 text-rose-800'],
            default => ['label' => $this->status, 'color' => 'bg-gray-100 text-gray-800'],
        };
    }
}
