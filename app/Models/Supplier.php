<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Supplier extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'category',
        'contact_person',
        'phone',
        'email',
        'address',
        'average_lead_time_days',
        'rating',
        'notes',
        'status',
    ];

    protected $casts = [
        'status' => 'boolean',
        'rating' => 'decimal:1',
    ];

    public function rawMaterials(): HasMany
    {
        return $this->hasMany(RawMaterial::class);
    }

    public function purchaseOrders(): HasMany
    {
        return $this->hasMany(PurchaseOrder::class);
    }
}
