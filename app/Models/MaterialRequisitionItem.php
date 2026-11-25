<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MaterialRequisitionItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'material_requisition_id',
        'raw_material_id',
        'custom_material_name',
        'unit',
        'quantity_requested',
        'quantity_issued',
        'quantity_missing',
        'unit_cost',
    ];

    protected $casts = [
        'quantity_requested' => 'decimal:2',
        'quantity_issued' => 'decimal:2',
        'quantity_missing' => 'decimal:2',
        'unit_cost' => 'decimal:4',
    ];

    public function getItemDisplayNameAttribute(): string
    {
        return $this->custom_material_name ?: ($this->rawMaterial?->name ?? 'مادة خام غير محددة');
    }

    public function getUnitDisplayAttribute(): string
    {
        return $this->unit ?: ($this->rawMaterial?->unit ?? 'قطعة');
    }

    public function materialRequisition(): BelongsTo
    {
        return $this->belongsTo(MaterialRequisition::class);
    }

    public function rawMaterial(): BelongsTo
    {
        return $this->belongsTo(RawMaterial::class);
    }
}
