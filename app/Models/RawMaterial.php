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
        'image',
        'package_info',
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

    public function getImageUrlAttribute(): string
    {
        if ($this->image && (str_starts_with($this->image, 'http://') || str_starts_with($this->image, 'https://'))) {
            return $this->image;
        }

        if ($this->image) {
            $cleanPath = ltrim(str_replace('\\', '/', $this->image), '/');
            if (file_exists(public_path($cleanPath)) || str_starts_with($cleanPath, 'uploads/') || str_starts_with($cleanPath, 'images/')) {
                return asset($cleanPath);
            }
        }

        return 'https://ui-avatars.com/api/?name='.urlencode($this->name).'&background=f59e0b&color=fff&size=256';
    }
}
