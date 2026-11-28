<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Machine extends Model
{
    use HasFactory;

    protected $fillable = [
        'factory_id',
        'name',
        'code',
        'model',
        'serial_number',
        'rated_speed_units_per_hour',
        'belt_speed_m_min',
        'status',
        'maintenance_due_date',
        'notes',
    ];

    protected $casts = [
        'rated_speed_units_per_hour' => 'decimal:2',
        'belt_speed_m_min' => 'decimal:2',
        'maintenance_due_date' => 'date',
    ];

    public function factory(): BelongsTo
    {
        return $this->belongsTo(Factory::class);
    }

    public function hourlyReports(): HasMany
    {
        return $this->hasMany(HourlyMachineReport::class);
    }

    public function productionOrders(): HasMany
    {
        return $this->hasMany(ProductionOrder::class);
    }

    public function taskAssignments(): HasMany
    {
        return $this->hasMany(TaskAssignment::class);
    }
}
