<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BillOfMaterial extends Model
{
    use HasFactory;

    protected $table = 'bill_of_materials';

    protected $fillable = [
        'product_id',
        'raw_material_id',
        'quantity_per_unit',
        'waste_percentage',
        'unit_cost_snapshot',
        'total_cost_per_unit',
        'notes',
    ];

    protected $casts = [
        'quantity_per_unit' => 'decimal:4',
        'waste_percentage' => 'decimal:2',
        'unit_cost_snapshot' => 'decimal:4',
        'total_cost_per_unit' => 'decimal:4',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function rawMaterial(): BelongsTo
    {
        return $this->belongsTo(RawMaterial::class);
    }
}
