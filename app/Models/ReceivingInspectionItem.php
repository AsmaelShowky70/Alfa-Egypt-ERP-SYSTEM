<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReceivingInspectionItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'receiving_inspection_id',
        'item_type',
        'item_id',
        'item_name',
        'unit',
        'delivered_quantity',
        'accepted_quantity',
        'rejected_quantity',
        'inspection_result',
        'notes',
    ];

    protected $casts = [
        'delivered_quantity' => 'decimal:3',
        'accepted_quantity' => 'decimal:3',
        'rejected_quantity' => 'decimal:3',
    ];

    public function inspection(): BelongsTo
    {
        return $this->belongsTo(ReceivingInspection::class, 'receiving_inspection_id');
    }

    public function getResultArabicAttribute(): string
    {
        return match ($this->inspection_result) {
            'accepted' => 'مطابق ومقبول',
            'partial' => 'قبول جزئي',
            'rejected' => 'مرفوض',
            default => $this->inspection_result,
        };
    }
}
