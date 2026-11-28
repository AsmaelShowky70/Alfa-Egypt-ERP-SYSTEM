<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaskFollowUp extends Model
{
    use HasFactory;

    protected $fillable = [
        'task_assignment_id',
        'user_id',
        'report_date',
        'notes',
        'downtime_minutes',
        'spare_parts_used',
        'machine_status',
        'progress_percentage',
    ];

    protected $casts = [
        'report_date' => 'date',
    ];

    public function taskAssignment(): BelongsTo
    {
        return $this->belongsTo(TaskAssignment::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
