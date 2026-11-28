<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TaskAssignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'assigned_by_user_id',
        'assigned_to_user_id',
        'machine_id',
        'title',
        'task_type',
        'priority',
        'status',
        'description',
        'due_date',
        'notes',
        'completed_at',
    ];

    protected $casts = [
        'due_date' => 'date',
        'completed_at' => 'datetime',
    ];

    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by_user_id');
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to_user_id');
    }

    public function machine(): BelongsTo
    {
        return $this->belongsTo(Machine::class);
    }

    public function followUps(): HasMany
    {
        return $this->hasMany(TaskFollowUp::class)->latest('report_date');
    }

    public function getTaskTypeArabicNameAttribute(): string
    {
        return match ($this->task_type) {
            'overtime' => 'عمل إضافي (Overtime)',
            'maintenance' => 'صيانة دورية ووقائية',
            'supervision' => 'إشراف ومتابعة تشغيل',
            'operational' => 'مهمة تشغيلية',
            default => 'تكليف إداري عام',
        };
    }

    public function getTaskTypeBadgeClassAttribute(): string
    {
        return match ($this->task_type) {
            'overtime' => 'bg-amber-100 text-amber-900 border-amber-300',
            'maintenance' => 'bg-blue-100 text-blue-900 border-blue-300',
            'supervision' => 'bg-purple-100 text-purple-900 border-purple-300',
            'operational' => 'bg-emerald-100 text-emerald-900 border-emerald-300',
            default => 'bg-slate-100 text-slate-800 border-slate-300',
        };
    }

    public function getPriorityArabicNameAttribute(): string
    {
        return match ($this->priority) {
            'urgent' => 'عاجل وحرج',
            'high' => 'أولوية مرتفعة',
            'normal' => 'أولوية متوسطة',
            'low' => 'أولوية عادية',
            default => 'عادية',
        };
    }

    public function getPriorityBadgeClassAttribute(): string
    {
        return match ($this->priority) {
            'urgent' => 'bg-rose-100 text-rose-800 border-rose-300',
            'high' => 'bg-orange-100 text-orange-800 border-orange-300',
            'normal' => 'bg-blue-100 text-blue-800 border-blue-200',
            default => 'bg-slate-100 text-slate-700 border-slate-200',
        };
    }

    public function getStatusArabicNameAttribute(): string
    {
        return match ($this->status) {
            'in_progress' => 'قيد التنفيذ والمتابعة',
            'completed' => 'مكتمل ومعتمد',
            'pending' => 'بانتظار البدء',
            'paused' => 'متوقف مؤقتاً',
            default => 'قيد التنفيذ',
        };
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'in_progress' => 'bg-emerald-100 text-emerald-800 border-emerald-300',
            'completed' => 'bg-blue-100 text-blue-800 border-blue-300',
            'pending' => 'bg-amber-100 text-amber-800 border-amber-300',
            'paused' => 'bg-rose-100 text-rose-800 border-rose-300',
            default => 'bg-slate-100 text-slate-700',
        };
    }
}
