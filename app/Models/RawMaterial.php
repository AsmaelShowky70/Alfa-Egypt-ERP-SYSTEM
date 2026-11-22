<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RawMaterial extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'category',
        'unit',
        'unit_cost',
        'current_stock',
        'reorder_level',
        'warehouse_location',
        'supplier_id',
        'source_factory_id',
        'status',
    ];

    protected $casts = [
        'unit_cost' => 'decimal:4',
        'current_stock' => 'decimal:2',
        'reorder_level' => 'decimal:2',
        'status' => 'boolean',
    ];

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function sourceFactory(): BelongsTo
    {
        return $this->belongsTo(Factory::class, 'source_factory_id');
    }

    public function boms(): HasMany
    {
        return $this->hasMany(BillOfMaterial::class);
    }

    public function isLowStock(): bool
    {
        return $this->current_stock <= $this->reorder_level;
    }
}
