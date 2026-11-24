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
        'received_by',
        'requested_at',
        'issued_at',
        'received_at',
        'notes',
    ];

    protected $casts = [
        'requested_at' => 'datetime',
        'issued_at' => 'datetime',
        'received_at' => 'datetime',
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

    public function receivedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    public function getStatusBadgeAttribute(): array
    {
        return match($this->status) {
            'received' => ['label' => 'تم الاستلام بالمصنع', 'color' => 'bg-emerald-100 text-emerald-800 border-emerald-300'],
            'issued' => ['label' => 'تم الصرف - بانتظار استلام المصنع', 'color' => 'bg-blue-100 text-blue-800 border-blue-300'],
            'partial_issued' => ['label' => 'صرف جزئي', 'color' => 'bg-amber-100 text-amber-800 border-amber-300'],
            'rejected' => ['label' => 'مرفوض', 'color' => 'bg-rose-100 text-rose-800 border-rose-300'],
            default => ['label' => 'بانتظار موافقة وصرف المخزن', 'color' => 'bg-amber-50 text-amber-700 border-amber-200'],
        };
    }

    public function items(): HasMany
    {
        return $this->hasMany(MaterialRequisitionItem::class);
    }
}
