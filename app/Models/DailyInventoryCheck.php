<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DailyInventoryCheck extends Model
{
    use HasFactory;

    protected $fillable = [
        'check_code',
        'check_date',
        'warehouse_manager_id',
        'category',
        'status',
        'total_surplus_value',
        'total_deficit_value',
        'notes',
    ];

    protected $casts = [
        'check_date' => 'date',
        'total_surplus_value' => 'decimal:2',
        'total_deficit_value' => 'decimal:2',
    ];

    public function warehouseManager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'warehouse_manager_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(DailyInventoryCheckItem::class);
    }

    public function getCategoryArabicAttribute(): string
    {
        return match ($this->category) {
            'raw_materials' => 'المواد الخام ومستلزمات الإنتاج',
            'finished_goods' => 'المنتج التام (الكوڤير والأكياس)',
            'spare_parts' => 'قطع الغيار ومستلزمات الصيانة',
            default => $this->category,
        };
    }
}
