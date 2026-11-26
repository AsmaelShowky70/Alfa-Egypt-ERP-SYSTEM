<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Add opening balance to Customers table
        Schema::table('customers', function (Blueprint $table) {
            if (! Schema::hasColumn('customers', 'opening_balance')) {
                $table->decimal('opening_balance', 14, 2)->default(0)->after('payment_terms');
            }
        });

        // 2. Add opening balance to Suppliers table
        Schema::table('suppliers', function (Blueprint $table) {
            if (! Schema::hasColumn('suppliers', 'opening_balance')) {
                $table->decimal('opening_balance', 14, 2)->default(0)->after('category');
            }
        });

        // 3. Add double-entry, customer, supplier, and tax tracking to Financial Transactions
        Schema::table('financial_transactions', function (Blueprint $table) {
            if (! Schema::hasColumn('financial_transactions', 'customer_id')) {
                $table->foreignId('customer_id')->nullable()->after('payment_method')->constrained('customers')->nullOnDelete();
            }
            if (! Schema::hasColumn('financial_transactions', 'supplier_id')) {
                $table->foreignId('supplier_id')->nullable()->after('customer_id')->constrained('suppliers')->nullOnDelete();
            }
            if (! Schema::hasColumn('financial_transactions', 'debit_account')) {
                $table->string('debit_account')->nullable()->after('supplier_id');
            }
            if (! Schema::hasColumn('financial_transactions', 'credit_account')) {
                $table->string('credit_account')->nullable()->after('debit_account');
            }
            if (! Schema::hasColumn('financial_transactions', 'tax_vat_amount')) {
                $table->decimal('tax_vat_amount', 14, 2)->default(0)->after('credit_account');
            }
            if (! Schema::hasColumn('financial_transactions', 'tax_withholding_amount')) {
                $table->decimal('tax_withholding_amount', 14, 2)->default(0)->after('tax_vat_amount');
            }
            if (! Schema::hasColumn('financial_transactions', 'net_amount')) {
                $table->decimal('net_amount', 14, 2)->nullable()->after('tax_withholding_amount');
            }
            if (! Schema::hasColumn('financial_transactions', 'check_number')) {
                $table->string('check_number')->nullable()->after('net_amount');
            }
            if (! Schema::hasColumn('financial_transactions', 'due_date')) {
                $table->date('due_date')->nullable()->after('check_number');
            }
            if (! Schema::hasColumn('financial_transactions', 'status')) {
                $table->string('status')->default('completed')->after('due_date');
            }
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            if (Schema::hasColumn('customers', 'opening_balance')) {
                $table->dropColumn('opening_balance');
            }
        });

        Schema::table('suppliers', function (Blueprint $table) {
            if (Schema::hasColumn('suppliers', 'opening_balance')) {
                $table->dropColumn('opening_balance');
            }
        });

        Schema::table('financial_transactions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('customer_id');
            $table->dropConstrainedForeignId('supplier_id');
            $table->dropColumn([
                'debit_account',
                'credit_account',
                'tax_vat_amount',
                'tax_withholding_amount',
                'net_amount',
                'check_number',
                'due_date',
                'status',
            ]);
        });
    }
};
