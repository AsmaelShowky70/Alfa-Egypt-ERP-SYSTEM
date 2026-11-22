<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'category',
        'type',
        'unit',
        'factory_id',
        'customer_id',
        'cost_price',
        'selling_price',
        'min_selling_price',
        'current_stock',
        'reorder_level',
        'image',
        'description',
        'status',
    ];

    protected $casts = [
        'cost_price' => 'decimal:4',
        'selling_price' => 'decimal:4',
        'min_selling_price' => 'decimal:4',
        'current_stock' => 'decimal:2',
        'reorder_level' => 'decimal:2',
        'status' => 'boolean',
    ];

    public function factory(): BelongsTo
    {
        return $this->belongsTo(Factory::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function boms(): HasMany
    {
        return $this->hasMany(BillOfMaterial::class);
    }

    public function salesOrderItems(): HasMany
    {
        return $this->hasMany(SalesOrderItem::class);
    }

    public function productionOrders(): HasMany
    {
        return $this->hasMany(ProductionOrder::class);
    }

    /**
     * Recalculate cost based on BOM components
     */
    public function recalculateCostFromBom(): float
    {
        $totalCost = 0;
        foreach ($this->boms()->with('rawMaterial')->get() as $item) {
            $cost = $item->rawMaterial ? $item->rawMaterial->unit_cost : $item->unit_cost_snapshot;
            $sub = $cost * $item->quantity_per_unit * (1 + ($item->waste_percentage / 100));
            $item->update([
                'unit_cost_snapshot' => $cost,
                'total_cost_per_unit' => $sub,
            ]);
            $totalCost += $sub;
        }

        $this->update(['cost_price' => $totalCost]);
        return $totalCost;
    }

    public function getMarginPercentageAttribute(): float
    {
        if ($this->selling_price <= 0) return 0;
        return round((($this->selling_price - $this->cost_price) / $this->selling_price) * 100, 2);
    }

    public function getHasNegativeMarginAttribute(): bool
    {
        return $this->selling_price < $this->cost_price;
    }

    public function getImageUrlAttribute(): string
    {
        if ($this->image && (str_starts_with($this->image, 'http://') || str_starts_with($this->image, 'https://'))) {
            return $this->image;
        }
        if ($this->image && file_exists(public_path($this->image))) {
            return asset($this->image);
        }
        return 'https://placehold.co/400x300/e2ede9/008060?text=' . urlencode($this->name);
    }
}
