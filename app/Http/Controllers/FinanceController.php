<?php

namespace App\Http\Controllers;

use App\Models\Factory;
use App\Models\FinancialTransaction;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\SalesOrder;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class FinanceController extends Controller
{
    public function index(): View
    {
        $transactions = FinancialTransaction::with(['salesOrder.customer', 'purchaseOrder.supplier', 'factory'])
            ->latest()
            ->paginate(15);

        $totalRevenue = FinancialTransaction::where('type', 'customer_payment')->sum('amount');
        $fixedCosts = FinancialTransaction::where('type', 'fixed_cost')->sum('amount');
        $variableCosts = FinancialTransaction::where('type', 'variable_cost')->sum('amount');
        $markup14Total = FinancialTransaction::where('type', 'factory_internal_14_markup')->sum('amount');

        $netProfit = $totalRevenue - ($fixedCosts + $variableCosts);

        $factories = Factory::all();
        $factoryPL = [];
        foreach ($factories as $f) {
            $rev = FinancialTransaction::where('factory_id', $f->id)->whereIn('type', ['customer_payment', 'factory_internal_14_markup'])->sum('amount');
            $cost = FinancialTransaction::where('factory_id', $f->id)->whereIn('type', ['variable_cost', 'fixed_cost'])->sum('amount');
            $factoryPL[] = [
                'factory' => $f,
                'revenue' => (float)$rev,
                'cost' => (float)$cost,
                'profit' => (float)($rev - $cost),
            ];
        }

        $lossAlarms = SalesOrder::with(['customer', 'items.product'])
            ->where('has_loss_alarm', true)
            ->latest()
            ->get();

        return view('finance.index', compact(
            'transactions',
            'totalRevenue',
            'fixedCosts',
            'variableCosts',
            'markup14Total',
            'netProfit',
            'factoryPL',
            'lossAlarms'
        ));
    }

    // ================= TRANSACTIONS =================
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
            $filename = 'receipt_' . time() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/receipts'), $filename);
            $attachmentPath = 'uploads/receipts/' . $filename;
        }

        $txnNumber = 'FT-' . date('Ymd') . '-' . str_pad((FinancialTransaction::count() + 1), 3, '0', STR_PAD_LEFT);

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

        return redirect()->route('finance.index')->with('success', 'تم ترحيل المعاملة المالية رقم ' . $txnNumber . ' بنجاح.');
    }

    // ================= COSTING & PRICING ENGINE =================
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

        return back()->with('success', 'تم تحديث التسعير وهوامش الربح للمنتج ' . $product->name . ' بنجاح.');
    }

    public function recalculateProductCost(Product $product): RedirectResponse
    {
        $cost = $product->recalculateCostFromBom();
        return back()->with('success', 'تمت إعادة احتساب التكلفة المباشرة من واقع بطاقة المكونات (BOM) وأصبحت: ' . number_format($cost, 4) . ' ج.م');
    }

    // ================= LOSS ALARMS (مراقبة الخسائر وإنذارات الأسعار) =================
    public function lossAlarms(): View
    {
        $lossOrders = SalesOrder::with(['customer', 'items.product.boms.rawMaterial'])
            ->where('has_loss_alarm', true)
            ->latest()
            ->get();

        return view('finance.loss_alarms', compact('lossOrders'));
    }
}
