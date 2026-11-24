<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HourlyMachineReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'machine_id',
        'factory_id',
        'production_order_id',
        'product_id',
        'operator_name',
        'report_date',
        'hour_slot',
        'units_produced',
        'belt_speed_m_min',
        'efficiency_rate',
        'waste_units',
        'downtime_minutes',
        'downtime_reason',
        'actions_taken',
        'ai_smart_recommendation',
        'submitted_to_management',
        'created_by',
    ];

    protected $casts = [
        'report_date' => 'date',
        'units_produced' => 'decimal:2',
        'belt_speed_m_min' => 'decimal:2',
        'efficiency_rate' => 'decimal:2',
        'waste_units' => 'decimal:2',
        'submitted_to_management' => 'boolean',
    ];

    public function machine(): BelongsTo
    {
        return $this->belongsTo(Machine::class);
    }

    public function factory(): BelongsTo
    {
        return $this->belongsTo(Factory::class);
    }

    public function productionOrder(): BelongsTo
    {
        return $this->belongsTo(ProductionOrder::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function getActiveProductAttribute(): ?Product
    {
        return $this->product ?? $this->productionOrder?->product;
    }

    public function getIsEditableAttribute(): bool
    {
        $todayStr = now()->toDateString();
        $isSameDay = $this->report_date && $this->report_date->toDateString() === $todayStr;
        $isCreatedToday = $this->created_at && $this->created_at->toDateString() === $todayStr;

        // Editable during the same day of the shift, or if senior management/owner
        if ($isSameDay || $isCreatedToday) {
            return true;
        }

        return auth()->user()?->hasRole(['owner', 'general_manager']) ?? false;
    }

    public function createdByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Generate smart diagnostic recommendation based on machine hourly metrics
     */
    public static function generateRecommendation(float $efficiency, int $downtime, float $waste, float $speed, float $ratedSpeed): string
    {
        $notes = [];
        if ($efficiency >= 90) {
            $notes[] = "الأداء ممتاز والماكينة تعمل بكفاءة تشغيلية مثالية أعلى من 90%. يوصى بالحفاظ على نفس معدل التغذية ونظافة السير.";
        } elseif ($efficiency >= 75) {
            $notes[] = "الكفاءة متوسطة (مقبولة). تم رصد تفاوت طفيف في معدل الإنتاج؛ يرجى فحص ضغط الهواء وتوافق سرعة السير.";
        } else {
            $notes[] = "تنبيه تشغيلي حرج: كفاءة الماكينة منخفضة جداً دون الحد الأدنى. توقفات متكررة أو هادر مرتفع يتطلب تدخل فوري من مهندس الصيانة.";
        }

        if ($waste > 20) {
            $notes[] = "ارتفاع ملحوظ في الهادر ({$waste} وحدة)؛ يوصى بضبط سكينة القص وحرارة لحام السيلوفان فوراً لمنع هدر الخامات.";
        }

        if ($downtime > 15) {
            $notes[] = "تم تسجيل توقف تشغيلي {$downtime} دقيقة خلال هذه الساعة؛ يرجى مراجعة سبب التوقف والتنسيق مع مدير الصيانة.";
        }

        if ($speed < ($ratedSpeed * 0.8)) {
            $notes[] = "سرعة السير الحالية أقل من السرعة المعيارية المصممة للماكينة بنسبة 20%. إمكانية رفع السرعة تدريجياً لزيادة الطاقة الإنتاجية.";
        }

        return implode(' ', $notes);
    }
}
