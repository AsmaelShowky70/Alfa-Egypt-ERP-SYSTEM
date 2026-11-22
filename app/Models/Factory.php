<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Factory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'type', // internal, outsourced
        'outsource_supplier_id',
        'supervisor_name',
        'supervisor_id',
        'operating_cost_per_hour',
        'markup_percentage',
        'status',
        'description',
    ];

    protected $casts = [
        'operating_cost_per_hour' => 'decimal:2',
        'markup_percentage' => 'decimal:2',
    ];

    public function machines(): HasMany
    {
        return $this->hasMany(Machine::class);
    }

    public function supervisor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'supervisor_id');
    }

    public function outsourceSupplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'outsource_supplier_id');
    }

    public function productionOrders(): HasMany
    {
        return $this->hasMany(ProductionOrder::class);
    }

    public function hourlyReports(): HasMany
    {
        return $this->hasMany(HourlyMachineReport::class);
    }

    public function financialTransactions(): HasMany
    {
        return $this->hasMany(FinancialTransaction::class);
    }

    public function rawMaterialsProduced(): HasMany
    {
        return $this->hasMany(RawMaterial::class, 'source_factory_id');
    }
}
