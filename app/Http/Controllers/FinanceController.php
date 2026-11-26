<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Factory;
use App\Models\FinancialTransaction;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\SalesOrder;
use App\Models\Supplier;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class FinanceController extends Controller
{
    public const EXPENSE_CATEGORIES = [
        'سولار ووقود مولدات وسيارات',
        'صيانة ونظافة مصانع',
        'إكراميات وعمالة يومية إضافية',
        'أدوات مكتبية ومطبوعات وفواتير',
        'نثريات وضيافة ووجبات عمال',
        'شحن ونقل ومشالات بضائع',
        'عهد وسلف مؤقتة',
        'أدوية وإسعافات أولية ومهمات وقاية',
        'فواتير كهرباء ومياه وغاز',
        'إيجار مصانع ومخازن',
        'تأمينات اجتماعية وصحية',
        'فواتير هواتف وإنترنت واتصالات',
        'استشارات قانونية ومحاسبية وتراخيص',
        'مصاريف ورسوم بنكية وعمولات تحصيل',
        'عمولات مبيعات وتسويق خارجي',
        'أكياس ومواد تغليف مستهلكة للمصنع',
        'قطع غيار عدد وأدوات مستهلكة',
        'مصروفات إدارية وعمومية متنوعة',
    ];

    /**
     * Financial Executive Dashboard
     */
    public function index(): View
    {
        $transactions = FinancialTransaction::with(['salesOrder.customer', 'purchaseOrder.supplier', 'customer', 'supplier', 'factory'])
            ->latest('transaction_date')
            ->latest('id')
            ->paginate(15);

        // Treasury Liquidity & Balances
        $cashInflows = FinancialTransaction::whereIn('type', ['customer_payment', 'factory_internal_14_markup'])
            ->sum('amount');
        $cashOutflows = FinancialTransaction::whereIn('type', ['supplier_payment', 'expense', 'fixed_cost', 'variable_cost'])
            ->sum('amount');
        $treasuryBalance = $cashInflows - $cashOutflows;

        // Total Receivables & Payables across all accounts
        $allCustomers = Customer::with('financialTransactions')->get();
        $totalReceivables = 0;
        $totalCustomerPrepayments = 0;
        foreach ($allCustomers as $c) {
            $bal = $c->current_balance;
            if ($bal > 0) {
                $totalReceivables += $bal;
            } else {
                $totalCustomerPrepayments += abs($bal);
            }
        }

        $allSuppliers = Supplier::with('financialTransactions')->get();
        $totalPayables = 0;
        $totalSupplierAdvances = 0;
        foreach ($allSuppliers as $s) {
            $bal = $s->current_balance;
            if ($bal > 0) {
                $totalPayables += $bal;
            } else {
                $totalSupplierAdvances += abs($bal);
            }
        }

        $totalRevenue = FinancialTransaction::where('type', 'customer_payment')->sum('amount');
        $totalSalesInvoiced = FinancialTransaction::where('type', 'sales_invoice')->sum('amount');
        $totalSupplierBills = FinancialTransaction::where('type', 'supplier_bill')->sum('amount');
        $totalGeneralExpenses = FinancialTransaction::where('type', 'expense')->sum('amount');
        $fixedCosts = FinancialTransaction::where('type', 'fixed_cost')->sum('amount');
        $variableCosts = FinancialTransaction::where('type', 'variable_cost')->sum('amount');
        $markup14Total = FinancialTransaction::where('type', 'factory_internal_14_markup')->sum('amount');

        $netProfit = ($totalSalesInvoiced + $markup14Total) - ($totalSupplierBills + $totalGeneralExpenses + $fixedCosts + $variableCosts);

        // Factory P&L
        $factories = Factory::all();
        $factoryPL = [];
        foreach ($factories as $f) {
            $rev = FinancialTransaction::where('factory_id', $f->id)->whereIn('type', ['customer_payment', 'factory_internal_14_markup'])->sum('amount');
            $cost = FinancialTransaction::where('factory_id', $f->id)->whereIn('type', ['variable_cost', 'fixed_cost', 'expense'])->sum('amount');
            $factoryPL[] = [
                'factory' => $f,
                'revenue' => (float) $rev,
                'cost' => (float) $cost,
                'profit' => (float) ($rev - $cost),
            ];
        }

        $lossAlarms = SalesOrder::with(['customer', 'items.product'])
            ->where('has_loss_alarm', true)
            ->latest()
            ->get();

        return view('finance.index', compact(
            'transactions',
            'treasuryBalance',
            'cashInflows',
            'cashOutflows',
            'totalReceivables',
            'totalCustomerPrepayments',
            'totalPayables',
            'totalSupplierAdvances',
            'totalRevenue',
            'totalSalesInvoiced',
            'totalSupplierBills',
            'totalGeneralExpenses',
            'fixedCosts',
            'variableCosts',
            'markup14Total',
            'netProfit',
            'factoryPL',
            'lossAlarms'
        ));
    }

    // ================= CUSTOMER RECEIVABLES & COLLECTIONS =================
    public function customers(Request $request): View
    {
        $query = Customer::with(['financialTransactions', 'salesOrders']);

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                    ->orWhere('code', 'like', "%{$s}%")
                    ->orWhere('phone', 'like', "%{$s}%");
            });
        }

        $customers = $query->orderBy('name')->get();

        // Filter by balance status if requested
        if ($request->filled('filter')) {
            if ($request->filter === 'debtor') {
                $customers = $customers->filter(fn ($c) => $c->current_balance > 0);
            } elseif ($request->filter === 'creditor') {
                $customers = $customers->filter(fn ($c) => $c->current_balance < 0);
            } elseif ($request->filter === 'settled') {
                $customers = $customers->filter(fn ($c) => round($c->current_balance, 2) == 0);
            }
        }

        $totalReceivables = 0;
        $totalCollected = 0;
        $totalAdvances = 0;

        foreach ($customers as $c) {
            $bal = $c->current_balance;
            if ($bal > 0) {
                $totalReceivables += $bal;
            } elseif ($bal < 0) {
                $totalAdvances += abs($bal);
            }
            $totalCollected += $c->total_collected;
        }

        return view('finance.customers.index', compact(
            'customers',
            'totalReceivables',
            'totalCollected',
            'totalAdvances'
        ));
    }

    public function customerStatement(Customer $customer): View
    {
        $transactions = FinancialTransaction::where('customer_id', $customer->id)
            ->orderBy('transaction_date', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        return view('finance.customers.statement', compact('customer', 'transactions'));
    }

    public function storeCustomerPayment(Request $request, Customer $customer): RedirectResponse
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|string',
            'transaction_date' => 'required|date',
            'check_number' => 'nullable|string',
            'due_date' => 'nullable|date',
            'description' => 'required|string',
            'attachment' => 'nullable|file|mimes:jpeg,png,jpg,pdf|max:4096',
        ]);

        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $filename = 'rec_cust_'.time().'.'.$file->getClientOriginalExtension();
            $file->move(public_path('uploads/receipts'), $filename);
            $attachmentPath = 'uploads/receipts/'.$filename;
        }

        $txnNumber = 'REC-'.date('Ymd').'-'.str_pad((FinancialTransaction::count() + 1), 4, '0', STR_PAD_LEFT);

        $debitAccount = match ($validated['payment_method']) {
            'شيك' => 'حـ/ أوراق قبض (شيكات تحت التحصيل)',
            'تحويل بنكي' => 'حـ/ الحساب البنكي',
            default => 'حـ/ الخزينة الرئيسية',
        };

        FinancialTransaction::create([
            'transaction_number' => $txnNumber,
            'type' => 'customer_payment',
            'category' => 'تحصيلات عملاء',
            'amount' => $validated['amount'],
            'transaction_date' => $validated['transaction_date'],
            'payment_method' => $validated['payment_method'],
            'customer_id' => $customer->id,
            'debit_account' => $debitAccount,
            'credit_account' => 'حـ/ العملاء - '.$customer->name,
            'check_number' => $validated['check_number'] ?? null,
            'due_date' => $validated['due_date'] ?? null,
            'description' => $validated['description'],
            'status' => 'posted',
            'attachment' => $attachmentPath,
            'created_by' => Auth::id(),
        ]);

        return back()->with('success', 'تم تسجيل سند القبض والتحصيل للعميل ('.$customer->name.') بنجاح برقم: '.$txnNumber);
    }

    // ================= SUPPLIER PAYABLES & ADVANCES =================
    public function suppliers(Request $request): View
    {
        $query = Supplier::with(['financialTransactions', 'purchaseOrders']);

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                    ->orWhere('code', 'like', "%{$s}%")
                    ->orWhere('category', 'like', "%{$s}%");
            });
        }

        $suppliers = $query->orderBy('name')->get();

        if ($request->filled('filter')) {
            if ($request->filter === 'creditor') {
                $suppliers = $suppliers->filter(fn ($s) => $s->current_balance > 0);
            } elseif ($request->filter === 'debtor') {
                $suppliers = $suppliers->filter(fn ($s) => $s->current_balance < 0);
            } elseif ($request->filter === 'settled') {
                $suppliers = $suppliers->filter(fn ($s) => round($s->current_balance, 2) == 0);
            }
        }

        $totalPayables = 0;
        $totalPaid = 0;
        $totalAdvances = 0;

        foreach ($suppliers as $s) {
            $bal = $s->current_balance;
            if ($bal > 0) {
                $totalPayables += $bal;
            } elseif ($bal < 0) {
                $totalAdvances += abs($bal);
            }
            $totalPaid += $s->total_paid;
        }

        return view('finance.suppliers.index', compact(
            'suppliers',
            'totalPayables',
            'totalPaid',
            'totalAdvances'
        ));
    }

    public function supplierStatement(Supplier $supplier): View
    {
        $transactions = FinancialTransaction::where('supplier_id', $supplier->id)
            ->orderBy('transaction_date', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        return view('finance.suppliers.statement', compact('supplier', 'transactions'));
    }

    public function storeSupplierPayment(Request $request, Supplier $supplier): RedirectResponse
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|string',
            'transaction_date' => 'required|date',
            'check_number' => 'nullable|string',
            'due_date' => 'nullable|date',
            'description' => 'required|string',
            'attachment' => 'nullable|file|mimes:jpeg,png,jpg,pdf|max:4096',
        ]);

        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $filename = 'pay_sup_'.time().'.'.$file->getClientOriginalExtension();
            $file->move(public_path('uploads/receipts'), $filename);
            $attachmentPath = 'uploads/receipts/'.$filename;
        }

        $txnNumber = 'PAY-'.date('Ymd').'-'.str_pad((FinancialTransaction::count() + 1), 4, '0', STR_PAD_LEFT);

        $creditAccount = match ($validated['payment_method']) {
            'شيك' => 'حـ/ أوراق دفع (شيكات صادرة للموردين)',
            'تحويل بنكي' => 'حـ/ الحساب البنكي',
            default => 'حـ/ الخزينة الرئيسية',
        };

        FinancialTransaction::create([
            'transaction_number' => $txnNumber,
            'type' => 'supplier_payment',
            'category' => 'سداد موردين',
            'amount' => $validated['amount'],
            'transaction_date' => $validated['transaction_date'],
            'payment_method' => $validated['payment_method'],
            'supplier_id' => $supplier->id,
            'debit_account' => 'حـ/ الموردين - '.$supplier->name,
            'credit_account' => $creditAccount,
            'check_number' => $validated['check_number'] ?? null,
            'due_date' => $validated['due_date'] ?? null,
            'description' => $validated['description'],
            'status' => 'posted',
            'attachment' => $attachmentPath,
            'created_by' => Auth::id(),
        ]);

        return back()->with('success', 'تم إصدار سند الصرف للمورد ('.$supplier->name.') بنجاح برقم: '.$txnNumber);
    }

    // ================= TREASURY & CASH MOVEMENTS =================
    public function treasury(Request $request): View
    {
        $query = FinancialTransaction::with(['customer', 'supplier', 'factory', 'createdByUser'])
            ->whereIn('type', ['customer_payment', 'supplier_payment', 'expense', 'fixed_cost', 'variable_cost', 'manual_journal', 'partner_drawing']);

        if ($request->filled('from_date')) {
            $query->whereDate('transaction_date', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->whereDate('transaction_date', '<=', $request->to_date);
        }

        $transactions = $query->latest('transaction_date')->latest('id')->paginate(20);

        // Overall Treasury Stats
        $inflows = FinancialTransaction::whereIn('type', ['customer_payment', 'factory_internal_14_markup'])->sum('amount');
        $outflows = FinancialTransaction::whereIn('type', ['supplier_payment', 'expense', 'fixed_cost', 'variable_cost'])->sum('amount');
        $treasuryBalance = $inflows - $outflows;

        // Current Month Stats
        $startOfMonth = Carbon::now()->startOfMonth();
        $monthInflows = FinancialTransaction::whereIn('type', ['customer_payment', 'factory_internal_14_markup'])
            ->whereDate('transaction_date', '>=', $startOfMonth)
            ->sum('amount');
        $monthOutflows = FinancialTransaction::whereIn('type', ['supplier_payment', 'expense', 'fixed_cost', 'variable_cost'])
            ->whereDate('transaction_date', '>=', $startOfMonth)
            ->sum('amount');

        return view('finance.treasury.index', compact(
            'transactions',
            'inflows',
            'outflows',
            'treasuryBalance',
            'monthInflows',
            'monthOutflows'
        ));
    }

    public function storeTreasuryVoucher(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'direction' => 'required|in:inflow,outflow',
            'category' => 'required|string',
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|string',
            'transaction_date' => 'required|date',
            'description' => 'required|string',
            'debit_account' => 'required|string',
            'credit_account' => 'required|string',
            'attachment' => 'nullable|file|mimes:jpeg,png,jpg,pdf|max:4096',
        ]);

        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $filename = 'tr_vch_'.time().'.'.$file->getClientOriginalExtension();
            $file->move(public_path('uploads/receipts'), $filename);
            $attachmentPath = 'uploads/receipts/'.$filename;
        }

        $prefix = $validated['direction'] === 'inflow' ? 'TR-IN-' : 'TR-OUT-';
        $txnNumber = $prefix.date('Ymd').'-'.str_pad((FinancialTransaction::count() + 1), 4, '0', STR_PAD_LEFT);
        $type = $validated['direction'] === 'inflow' ? 'customer_payment' : 'expense';

        FinancialTransaction::create([
            'transaction_number' => $txnNumber,
            'type' => $type,
            'category' => $validated['category'],
            'amount' => $validated['amount'],
            'transaction_date' => $validated['transaction_date'],
            'payment_method' => $validated['payment_method'],
            'debit_account' => $validated['debit_account'],
            'credit_account' => $validated['credit_account'],
            'description' => $validated['description'],
            'status' => 'posted',
            'attachment' => $attachmentPath,
            'created_by' => Auth::id(),
        ]);

        return back()->with('success', 'تم ترحيل سند الخزينة برقم '.$txnNumber.' بنجاح.');
    }

    // ================= GENERAL & OPERATING EXPENSES =================
    public function expenses(Request $request): View
    {
        $query = FinancialTransaction::with(['factory', 'createdByUser'])
            ->where(function ($q) {
                $q->where('type', 'expense')
                    ->orWhereIn('type', ['fixed_cost', 'variable_cost']);
            });

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }
        if ($request->filled('month')) {
            $m = Carbon::parse($request->month);
            $query->whereYear('transaction_date', $m->year)->whereMonth('transaction_date', $m->month);
        }

        $expenses = $query->latest('transaction_date')->latest('id')->paginate(20);

        // Calculate breakdown by category
        $categoryBreakdown = FinancialTransaction::where(function ($q) {
            $q->where('type', 'expense')->orWhereIn('type', ['fixed_cost', 'variable_cost']);
        })
            ->select('category', DB::raw('SUM(amount) as total_amount'), DB::raw('COUNT(*) as total_count'))
            ->groupBy('category')
            ->orderByDesc('total_amount')
            ->get();

        $grandTotal = $categoryBreakdown->sum('total_amount');
        $availableCategories = self::EXPENSE_CATEGORIES;
        $factories = Factory::all();

        return view('finance.expenses.index', compact(
            'expenses',
            'categoryBreakdown',
            'grandTotal',
            'availableCategories',
            'factories'
        ));
    }

    public function storeExpense(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'category' => 'required|string',
            'amount' => 'required|numeric|min:0.01',
            'transaction_date' => 'required|date',
            'payment_method' => 'required|string',
            'description' => 'required|string',
            'factory_id' => 'nullable|exists:factories,id',
            'attachment' => 'nullable|file|mimes:jpeg,png,jpg,pdf|max:4096',
        ]);

        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $filename = 'exp_'.time().'.'.$file->getClientOriginalExtension();
            $file->move(public_path('uploads/receipts'), $filename);
            $attachmentPath = 'uploads/receipts/'.$filename;
        }

        $txnNumber = 'EXP-'.date('Ymd').'-'.str_pad((FinancialTransaction::count() + 1), 4, '0', STR_PAD_LEFT);

        $creditAccount = match ($validated['payment_method']) {
            'شيك' => 'حـ/ أوراق دفع (شيكات)',
            'تحويل بنكي' => 'حـ/ الحساب البنكي',
            default => 'حـ/ الخزينة الرئيسية',
        };

        FinancialTransaction::create([
            'transaction_number' => $txnNumber,
            'type' => 'expense',
            'category' => $validated['category'],
            'amount' => $validated['amount'],
            'transaction_date' => $validated['transaction_date'],
            'payment_method' => $validated['payment_method'],
            'factory_id' => $validated['factory_id'] ?? null,
            'debit_account' => 'حـ/ المصروفات العمومية والتشغيلية - '.$validated['category'],
            'credit_account' => $creditAccount,
            'description' => $validated['description'],
            'status' => 'posted',
            'attachment' => $attachmentPath,
            'created_by' => Auth::id(),
        ]);

        return back()->with('success', 'تم ترحيل المصروف رقم '.$txnNumber.' بنجاح وإدراجه في قائمة التكاليف والخزينة.');
    }

    // ================= GENERAL JOURNAL DOUBLE-ENTRY LEDGER =================
    public function journal(Request $request): View
    {
        $query = FinancialTransaction::with(['customer', 'supplier', 'factory', 'createdByUser']);

        if ($request->filled('from_date')) {
            $query->whereDate('transaction_date', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->whereDate('transaction_date', '<=', $request->to_date);
        }
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $transactions = $query->latest('transaction_date')->latest('id')->paginate(25);

        $totalDebit = FinancialTransaction::sum('amount');
        $totalCredit = $totalDebit; // In balanced double-entry accounting

        return view('finance.journal.index', compact('transactions', 'totalDebit', 'totalCredit'));
    }

    public function storeManualJournal(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'debit_account' => 'required|string|max:255',
            'credit_account' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0.01',
            'transaction_date' => 'required|date',
            'description' => 'required|string',
            'attachment' => 'nullable|file|mimes:jpeg,png,jpg,pdf|max:4096',
        ]);

        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $filename = 'jrn_'.time().'.'.$file->getClientOriginalExtension();
            $file->move(public_path('uploads/receipts'), $filename);
            $attachmentPath = 'uploads/receipts/'.$filename;
        }

        $txnNumber = 'JRN-'.date('Ymd').'-'.str_pad((FinancialTransaction::count() + 1), 4, '0', STR_PAD_LEFT);

        FinancialTransaction::create([
            'transaction_number' => $txnNumber,
            'type' => 'manual_journal',
            'category' => 'تسوية قيد يومية عامة',
            'amount' => $validated['amount'],
            'transaction_date' => $validated['transaction_date'],
            'payment_method' => 'قيد دفتري',
            'debit_account' => $validated['debit_account'],
            'credit_account' => $validated['credit_account'],
            'description' => $validated['description'],
            'status' => 'posted',
            'attachment' => $attachmentPath,
            'created_by' => Auth::id(),
        ]);

        return back()->with('success', 'تم ترحيل قيد اليومية المزدوج رقم '.$txnNumber.' بنجاح ومطابقة الطرفين المدين والدائن.');
    }

    // ================= MONTHLY FINANCIAL ANALYSIS (OCTOBER REPLICA) =================
    public function octoberAnalysis(): View
    {
        // 1. Sheet 1: Sales & Customers (with 14% VAT and 1% withholding tax)
        $salesInvoices = FinancialTransaction::where('type', 'sales_invoice')
            ->whereYear('transaction_date', 2026)
            ->whereMonth('transaction_date', 10)
            ->with('customer')
            ->get();

        $totalOctoberSales = $salesInvoices->sum('amount');
        $totalOctoberVAT = $salesInvoices->sum('tax_vat_amount');
        $totalOctoberWithholding = $salesInvoices->sum('tax_withholding_amount');
        $totalOctoberNetSales = $salesInvoices->sum('net_amount');

        // Customer collections for October
        $octoberCollections = FinancialTransaction::where('type', 'customer_payment')
            ->whereYear('transaction_date', 2026)
            ->whereMonth('transaction_date', 10)
            ->with('customer')
            ->get();
        $totalOctoberCollected = $octoberCollections->sum('amount');

        // 2. Sheet 2: Purchases & Suppliers
        $supplierBills = FinancialTransaction::where('type', 'supplier_bill')
            ->whereYear('transaction_date', 2026)
            ->whereMonth('transaction_date', 10)
            ->with('supplier')
            ->get();
        $totalOctoberBills = $supplierBills->sum('amount');

        $supplierPayments = FinancialTransaction::where('type', 'supplier_payment')
            ->whereYear('transaction_date', 2026)
            ->whereMonth('transaction_date', 10)
            ->with('supplier')
            ->get();
        $totalOctoberPaidSuppliers = $supplierPayments->sum('amount');

        // 3. Sheet 3: General & Operating Expenses (م غمومية)
        $expenses = FinancialTransaction::where(function ($q) {
            $q->where('type', 'expense')->orWhereIn('type', ['fixed_cost', 'variable_cost']);
        })
            ->whereYear('transaction_date', 2026)
            ->whereMonth('transaction_date', 10)
            ->get();
        $totalOctoberExpenses = $expenses->sum('amount');

        $expenseCategoriesGrouped = FinancialTransaction::where(function ($q) {
            $q->where('type', 'expense')->orWhereIn('type', ['fixed_cost', 'variable_cost']);
        })
            ->whereYear('transaction_date', 2026)
            ->whereMonth('transaction_date', 10)
            ->select('category', DB::raw('SUM(amount) as total_amount'), DB::raw('COUNT(*) as total_count'))
            ->groupBy('category')
            ->orderByDesc('total_amount')
            ->get();

        // 4. Sheet 4: Treasury Cash Flow (Inflows vs Outflows and Deficit)
        $treasuryInflows = $totalOctoberCollected;
        $treasuryOutflows = $totalOctoberPaidSuppliers + $totalOctoberExpenses;
        $octoberDeficit = $treasuryInflows - $treasuryOutflows;

        return view('finance.analysis.october', compact(
            'salesInvoices',
            'totalOctoberSales',
            'totalOctoberVAT',
            'totalOctoberWithholding',
            'totalOctoberNetSales',
            'octoberCollections',
            'totalOctoberCollected',
            'supplierBills',
            'totalOctoberBills',
            'supplierPayments',
            'totalOctoberPaidSuppliers',
            'expenses',
            'totalOctoberExpenses',
            'expenseCategoriesGrouped',
            'treasuryInflows',
            'treasuryOutflows',
            'octoberDeficit'
        ));
    }

    // ================= LEGACY / PRICING & LOSS ALARMS =================
    public function createTransaction(): View
    {
        $factories = Factory::all();
        $salesOrders = SalesOrder::with('customer')->latest()->take(30)->get();
        $purchaseOrders = PurchaseOrder::latest()->take(30)->get();

        return view('finance.transactions.create', compact('factories', 'salesOrders', 'purchaseOrders'));
    }

    public function storeTransaction(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'type' => 'required|string',
            'category' => 'required|string',
            'amount' => 'required|numeric|min:0.01',
            'transaction_date' => 'required|date',
            'payment_method' => 'required|string',
            'description' => 'required|string',
            'factory_id' => 'nullable|exists:factories,id',
            'sales_order_id' => 'nullable|exists:sales_orders,id',
            'purchase_order_id' => 'nullable|exists:purchase_orders,id',
            'attachment' => 'nullable|file|mimes:jpeg,png,jpg,pdf|max:4096',
        ]);

        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $filename = 'receipt_'.time().'.'.$file->getClientOriginalExtension();
            $file->move(public_path('uploads/receipts'), $filename);
            $attachmentPath = 'uploads/receipts/'.$filename;
        }

        $txnNumber = 'FT-'.date('Ymd').'-'.str_pad((FinancialTransaction::count() + 1), 4, '0', STR_PAD_LEFT);

        FinancialTransaction::create([
            'transaction_number' => $txnNumber,
            'type' => $validated['type'],
            'category' => $validated['category'],
            'amount' => $validated['amount'],
            'transaction_date' => $validated['transaction_date'],
            'payment_method' => $validated['payment_method'],
            'description' => $validated['description'],
            'factory_id' => $validated['factory_id'],
            'sales_order_id' => $validated['sales_order_id'],
            'purchase_order_id' => $validated['purchase_order_id'],
            'attachment' => $attachmentPath,
            'created_by' => Auth::id(),
        ]);

        return redirect()->route('finance.index')->with('success', 'تم ترحيل المعاملة المالية رقم '.$txnNumber.' بنجاح.');
    }

    public function pricing(): View
    {
        $products = Product::with(['customer', 'boms.rawMaterial', 'factory'])->get();

        return view('finance.pricing.index', compact('products'));
    }

    public function updatePrice(Request $request, Product $product): RedirectResponse
    {
        $validated = $request->validate([
            'cost_price' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'min_selling_price' => 'required|numeric|min:0',
        ]);

        $product->update($validated);

        return back()->with('success', 'تم تحديث التسعير وهوامش الربح للمنتج '.$product->name.' بنجاح.');
    }

    public function recalculateProductCost(Product $product): RedirectResponse
    {
        $cost = $product->recalculateCostFromBom();

        return back()->with('success', 'تمت إعادة احتساب التكلفة المباشرة من واقع بطاقة المكونات (BOM) وأصبحت: '.number_format($cost, 4).' ج.م');
    }

    public function lossAlarms(): View
    {
        $lossOrders = SalesOrder::with(['customer', 'items.product.boms.rawMaterial'])
            ->where('has_loss_alarm', true)
            ->latest()
            ->get();

        return view('finance.loss_alarms', compact('lossOrders'));
    }
}
