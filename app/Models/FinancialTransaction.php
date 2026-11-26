<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FinancialTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_number',
        'type',
        'category',
        'amount',
        'transaction_date',
        'payment_method',
        'customer_id',
        'supplier_id',
        'sales_order_id',
        'purchase_order_id',
        'factory_id',
        'debit_account',
        'credit_account',
        'tax_vat_amount',
        'tax_withholding_amount',
        'net_amount',
        'check_number',
        'due_date',
        'status',
        'description',
        'attachment',
        'created_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'tax_vat_amount' => 'decimal:2',
        'tax_withholding_amount' => 'decimal:2',
        'net_amount' => 'decimal:2',
        'transaction_date' => 'date',
        'due_date' => 'date',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function salesOrder(): BelongsTo
    {
        return $this->belongsTo(SalesOrder::class);
    }

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function factory(): BelongsTo
    {
        return $this->belongsTo(Factory::class);
    }

    public function createdByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public static function generateTransactionNumber(string $prefix = 'FT'): string
    {
        $dateStr = date('Ymd');
        $nextId = (int) (static::max('id') ?? 0) + 1;
        do {
            $num = $prefix.'-'.$dateStr.'-'.str_pad($nextId, 4, '0', STR_PAD_LEFT);
            $nextId++;
        } while (static::where('transaction_number', $num)->exists());

        return $num;
    }

    public function getTypeNameAttribute(): string
    {
        return match ($this->type) {
            'sales_invoice' => 'فاتورة مبيعات',
            'customer_payment' => 'سند قبض / تحصيل عميل',
            'supplier_bill' => 'فاتورة مشتريات / خامات',
            'supplier_payment' => 'سند صرف / سداد مورد',
            'expense' => 'مصروف عمومي وتشغيلي',
            'factory_internal_14_markup' => 'تسوية تشغيل داخلي 14%',
            'fixed_cost' => 'تكلفة ثابتة',
            'variable_cost' => 'تكلفة متغيرة',
            'manual_journal' => 'قيد يومية يدوي',
            'partner_drawing' => 'جاري شركاء',
            'loan_repayment' => 'سداد قروض',
            default => $this->type,
        };
    }

    public function getBadgeClassAttribute(): string
    {
        return match ($this->type) {
            'sales_invoice' => 'bg-blue-100 text-blue-800 border-blue-200',
            'customer_payment' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
            'supplier_bill' => 'bg-purple-100 text-purple-800 border-purple-200',
            'supplier_payment' => 'bg-amber-100 text-amber-800 border-amber-200',
            'expense' => 'bg-rose-100 text-rose-800 border-rose-200',
            default => 'bg-slate-100 text-slate-800 border-slate-200',
        };
    }
}
