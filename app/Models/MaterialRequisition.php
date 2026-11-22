<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MaterialRequisition extends Model
{
    use HasFactory;

    protected $fillable = [
        'req_number',
        'production_order_id',
        'status',
        'requested_by',
        'issued_by',
        'requested_at',
        'issued_at',
        'notes',
    ];

    protected $casts = [
        'requested_at' => 'datetime',
        'issued_at' => 'datetime',
    ];

    public function productionOrder(): BelongsTo
    {
        return $this->belongsTo(ProductionOrder::class);
    }

    public function requestedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function issuedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'issued_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(MaterialRequisitionItem::class);
    }
}
