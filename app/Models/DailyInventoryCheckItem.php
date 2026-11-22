<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DailyInventoryCheckItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'daily_inventory_check_id',
        'item_type',
        'item_id',
        'item_name',
        'unit',
        'system_quantity',
        'actual_quantity',
        'difference',
        'unit_cost',
        'financial_impact',
        'reason',
    ];

    protected $casts = [
        'system_quantity' => 'decimal:2',
        'actual_quantity' => 'decimal:2',
        'difference' => 'decimal:2',
        'unit_cost' => 'decimal:4',
        'financial_impact' => 'decimal:2',
    ];

    public function dailyInventoryCheck(): BelongsTo
    {
        return $this->belongsTo(DailyInventoryCheck::class);
    }
}
