<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WarehouseArchivedReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'report_number',
        'report_type',
        'title',
        'from_date',
        'to_date',
        'item_type',
        'item_id',
        'item_name',
        'party_type',
        'party_id',
        'party_name',
        'total_quantity',
        'total_value',
        'items_count',
        'data_payload',
        'warehouse_manager_id',
        'warehouse_manager_signature',
        'recipient_name',
        'recipient_title',
        'recipient_signature',
        'recipient_phone',
        'is_signed_by_recipient',
        'notes',
    ];

    protected $casts = [
        'from_date' => 'date',
        'to_date' => 'date',
        'total_quantity' => 'decimal:2',
        'total_value' => 'decimal:2',
        'data_payload' => 'array',
        'is_signed_by_recipient' => 'boolean',
    ];

    public function warehouseManager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'warehouse_manager_id');
    }
}
