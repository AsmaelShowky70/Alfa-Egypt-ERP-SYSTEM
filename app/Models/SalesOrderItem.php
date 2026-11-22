<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalesOrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'sales_order_id',
        'product_id',
        'quantity',
        'unit_price',
        'unit_cost',
        'subtotal',
        'total_cost',
        'has_loss_item',
        'customization_notes',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_price' => 'decimal:4',
        'unit_cost' => 'decimal:4',
        'subtotal' => 'decimal:2',
        'total_cost' => 'decimal:2',
        'has_loss_item' => 'boolean',
    ];

    public function salesOrder(): BelongsTo
    {
        return $this->belongsTo(SalesOrder::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
