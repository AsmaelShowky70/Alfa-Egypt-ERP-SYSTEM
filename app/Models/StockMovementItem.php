<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockMovementItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'stock_movement_id',
        'item_type', // raw_material, product
        'item_id',
        'item_name',
        'unit',
        'quantity',
        'unit_cost',
        'total_cost',
        'notes',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_cost' => 'decimal:4',
        'total_cost' => 'decimal:2',
    ];

    public function stockMovement(): BelongsTo
    {
        return $this->belongsTo(StockMovement::class);
    }
}
