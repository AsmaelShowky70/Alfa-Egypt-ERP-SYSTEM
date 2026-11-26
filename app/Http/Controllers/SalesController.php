<?php

namespace App\Http\Controllers;

use App\Models\BillOfMaterial;
use App\Models\Customer;
use App\Models\FinancialTransaction;
use App\Models\Product;
use App\Models\RawMaterial;
use App\Models\SalesOrder;
use App\Models\SalesOrderItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class SalesController extends Controller
{
    // ================= CUSTOMERS =================
    public function customers(): View
    {
        $customers = Customer::withCount(['salesOrders', 'products'])->latest()->get();

        return view('sales.customers.index', compact('customers'));
    }

    public function createCustomer(): View
    {
        return view('sales.customers.create');
    }

    public function storeCustomer(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:customers,code',
            'contact_person' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string|max:500',
            'tax_number' => 'nullable|string|max:100',
            'commercial_register' => 'nullable|string|max:100',
            'payment_terms' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:3072',
            'logo_url' => 'nullable|url|max:500',
        ]);

        $logoPath = null;
        if ($request->hasFile('logo')) {
            $file = $request->file('logo');
            $filename = 'cust_'.time().'.'.$file->getClientOriginalExtension();
            $file->move(public_path('uploads/customers'), $filename);
            $logoPath = 'uploads/customers/'.$filename;
        } elseif (! empty($validated['logo_url'])) {
            $logoPath = $validated['logo_url'];
        }

        $customer = Customer::create([
            'name' => $validated['name'],
            'code' => $validated['code'],
            'contact_person' => $validated['contact_person'],
            'phone' => $validated['phone'],
            'email' => $validated['email'],
            'address' => $validated['address'],
            'tax_number' => $validated['tax_number'],
            'commercial_register' => $validated['commercial_register'],
            'payment_terms' => $validated['payment_terms'] ?? 'آجل 30 يوم',
            'notes' => $validated['notes'],
            'logo' => $logoPath,
            'status' => true,
        ]);

        return redirect()->route('sales.customers.show', $customer->id)
            ->with('success', 'تمت إضافة العميل الجديد بنجاح ويمكنك الآن تخصيص منتجات الكوڤير الخاصة به.');
    }

    public function showCustomer(Customer $customer): View
    {
        $customer->load(['products.boms.rawMaterial', 'salesOrders.items.product']);
        $rawMaterials = RawMaterial::where('status', true)->get();

        return view('sales.customers.show', compact('customer', 'rawMaterials'));
    }

    public function editCustomer(Customer $customer): View
    {
        return view('sales.customers.edit', compact('customer'));
    }

    public function updateCustomer(Request $request, Customer $customer): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:customers,code,'.$customer->id,
            'contact_person' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string|max:500',
            'tax_number' => 'nullable|string|max:100',
            'commercial_register' => 'nullable|string|max:100',
            'payment_terms' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
            'status' => 'required|boolean',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:3072',
            'logo_url' => 'nullable|url|max:500',
        ]);

        if ($request->hasFile('logo')) {
            $file = $request->file('logo');
            $filename = 'cust_'.time().'.'.$file->getClientOriginalExtension();
            $file->move(public_path('uploads/customers'), $filename);
            $customer->logo = 'uploads/customers/'.$filename;
        } elseif (! empty($validated['logo_url'])) {
            $customer->logo = $validated['logo_url'];
        }

        $customer->update([
            'name' => $validated['name'],
            'code' => $validated['code'],
            'contact_person' => $validated['contact_person'],
            'phone' => $validated['phone'],
            'email' => $validated['email'],
            'address' => $validated['address'],
            'tax_number' => $validated['tax_number'],
            'commercial_register' => $validated['commercial_register'],
            'payment_terms' => $validated['payment_terms'],
            'notes' => $validated['notes'],
            'status' => $validated['status'],
        ]);

        return redirect()->route('sales.customers.show', $customer->id)->with('success', 'تم تعديل بيانات العميل بنجاح.');
    }

    public function destroyCustomer(Customer $customer): RedirectResponse
    {
        if ($customer->salesOrders()->count() > 0) {
            return back()->with('error', 'لا يمكن حذف هذا العميل لوجود طلبات مبيعات مرتبطة به في النظام.');
        }

        $customer->delete();

        return redirect()->route('sales.customers')->with('success', 'تم حذف العميل بنجاح.');
    }

    // ================= CUSTOM PRODUCTS & BOM =================
    public function storeCustomProduct(Request $request, Customer $customer): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:products,code',
            'category' => 'required|string',
            'selling_price' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'materials' => 'required|array|min:1',
            'materials.*.id' => 'required|exists:raw_materials,id',
            'materials.*.qty' => 'required|numeric|min:0.01',
        ]);

        // Calculate cost from chosen materials
        $totalCost = 0;
        foreach ($validated['materials'] as $mat) {
            $rm = RawMaterial::find($mat['id']);
            $totalCost += ($rm->unit_cost * (float) $mat['qty']);
        }

        $product = Product::create([
            'code' => $validated['code'],
            'name' => $validated['name'],
            'category' => $validated['category'],
            'type' => 'finished_good',
            'unit' => 'قطعة',
            'customer_id' => $customer->id,
            'cost_price' => $totalCost,
            'selling_price' => $validated['selling_price'],
            'min_selling_price' => $totalCost * 1.10,
            'description' => $validated['description'],
            'status' => true,
        ]);

        foreach ($validated['materials'] as $mat) {
            $rm = RawMaterial::find($mat['id']);
            BillOfMaterial::create([
                'product_id' => $product->id,
                'raw_material_id' => $rm->id,
                'quantity_per_unit' => $mat['qty'],
                'waste_percentage' => 0,
                'unit_cost_snapshot' => $rm->unit_cost,
                'total_cost_per_unit' => $rm->unit_cost * (float) $mat['qty'],
            ]);
        }

        return back()->with('success', 'تم إنشاء تركيبة المنتج المخصص للعميل وتحديث تكلفته المباشرة بنجاح.');
    }

    // ================= SALES ORDERS =================
    public function orders(): View
    {
        $orders = SalesOrder::with(['customer', 'items.product', 'salesRep'])->latest()->paginate(15);
        $customers = Customer::where('status', true)->get();

        return view('sales.orders.index', compact('orders', 'customers'));
    }

    public function createOrder(): View
    {
        $customers = Customer::where('status', true)->with('products')->get();
        $products = Product::where('status', true)->with('boms.rawMaterial')->get();

        $productsJson = $products->map(function ($p) {
            return [
                'id' => $p->id,
                'name' => $p->name,
                'code' => $p->code,
                'customer_id' => $p->customer_id,
                'cost_price' => (float) $p->cost_price,
                'selling_price' => (float) $p->selling_price,
                'unit' => $p->unit ?? 'قطعة',
                'category' => $p->category,
                'boms' => $p->boms->map(fn ($b) => [
                    'name' => $b->rawMaterial->name ?? 'خامة',
                    'qty' => (float) $b->quantity_per_unit,
                    'cost' => (float) $b->total_cost_per_unit,
                ])->values(),
            ];
        })->values();

        $customersJson = $customers->map(function ($c) {
            return [
                'id' => $c->id,
                'name' => $c->name,
                'code' => $c->code,
                'products_count' => $c->products->count(),
            ];
        })->values();

        return view('sales.orders.create', compact('customers', 'products', 'productsJson', 'customersJson'));
    }

    public function storeOrder(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'order_date' => 'required|date',
            'target_delivery_date' => 'nullable|date',
            'client_notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.notes' => 'nullable|string',
        ]);

        $subtotal = 0;
        $totalCost = 0;
        $hasLossAlarm = false;

        $orderNumber = 'SO-'.date('Y').'-'.str_pad((SalesOrder::count() + 1), 3, '0', STR_PAD_LEFT);

        $order = SalesOrder::create([
            'order_number' => $orderNumber,
            'customer_id' => $validated['customer_id'],
            'sales_rep_id' => Auth::id(),
            'status' => 'pending_operations', // Forwarded to Operations management!
            'order_date' => $validated['order_date'],
            'target_delivery_date' => $validated['target_delivery_date'] ?? null,
            'client_notes' => $validated['client_notes'] ?? null,
            'subtotal' => 0,
            'tax' => 0,
            'discount' => 0,
            'total_amount' => 0,
            'total_estimated_cost' => 0,
            'profit_margin_percentage' => 0,
            'has_loss_alarm' => false,
            'payment_status' => 'unpaid',
        ]);

        foreach ($validated['items'] as $item) {
            $product = Product::findOrFail($item['product_id']);
            $qty = (float) $item['quantity'];
            $price = (float) $item['unit_price'];
            $cost = (float) $product->cost_price;

            $itemSub = $qty * $price;
            $itemCost = $qty * $cost;

            $itemHasLoss = ($price < $cost);
            if ($itemHasLoss) {
                $hasLossAlarm = true;
            }

            $subtotal += $itemSub;
            $totalCost += $itemCost;

            SalesOrderItem::create([
                'sales_order_id' => $order->id,
                'product_id' => $product->id,
                'quantity' => $qty,
                'unit_price' => $price,
                'unit_cost' => $cost,
                'subtotal' => $itemSub,
                'total_cost' => $itemCost,
                'has_loss_item' => $itemHasLoss,
                'customization_notes' => $item['notes'] ?? null,
            ]);
        }

        $tax = $subtotal * 0.14; // 14% ضريبة القيمة المضافة إن وجدت
        $totalAmount = $subtotal + $tax;
        $margin = $totalAmount > 0 ? round((($subtotal - $totalCost) / $subtotal) * 100, 2) : 0;

        $order->update([
            'subtotal' => $subtotal,
            'tax' => $tax,
            'total_amount' => $totalAmount,
            'total_estimated_cost' => $totalCost,
            'profit_margin_percentage' => $margin,
            'has_loss_alarm' => $hasLossAlarm,
        ]);

        // Auto-post double-entry Sales Invoice into Financial Ledger
        $customer = Customer::find($order->customer_id);
        $txnNumber = 'INV-'.date('Ymd').'-'.str_pad((FinancialTransaction::count() + 1), 4, '0', STR_PAD_LEFT);
        FinancialTransaction::create([
            'transaction_number' => $txnNumber,
            'type' => 'sales_invoice',
            'category' => 'إيرادات مبيعات',
            'amount' => $totalAmount,
            'net_amount' => $subtotal,
            'tax_vat_amount' => $tax,
            'tax_withholding_amount' => round($subtotal * 0.01, 2),
            'transaction_date' => $order->order_date,
            'payment_method' => 'آجل',
            'customer_id' => $order->customer_id,
            'sales_order_id' => $order->id,
            'debit_account' => 'حـ/ العملاء - '.($customer?->name ?? 'عميل'),
            'credit_account' => 'حـ/ إيرادات المبيعات',
            'description' => 'فاتورة مبيعات آلية عن طلب رقم '.$order->order_number,
            'status' => 'posted',
            'created_by' => Auth::id(),
        ]);

        $msg = 'تم تسجيل طلب المبيعات وإحالته فوراً إلى إدارة التشغيل وترحيل فاتورة المبيعات للحسابات.';
        if ($hasLossAlarm) {
            $msg .= ' ⚠️ تنبيه: تم تفعيل إنذار الخسارة نظراً لأن سعر بيع الكوڤير أقل من تكلفة الخامات المباشرة.';
        }

        return redirect()->route('sales.orders.show', $order->id)->with('success', $msg);
    }

    public function showOrder(SalesOrder $order): View
    {
        $order->load(['customer', 'salesRep', 'items.product', 'productionOrders', 'purchaseRequisitions']);

        return view('sales.orders.show', compact('order'));
    }

    public function confirmDeliveryDateToCustomer(Request $request, SalesOrder $order): RedirectResponse
    {
        $validated = $request->validate([
            'confirmed_delivery_date' => 'required|date',
            'internal_notes' => 'nullable|string',
        ]);

        $order->update([
            'confirmed_delivery_date' => $validated['confirmed_delivery_date'],
            'status' => 'confirmed',
            'internal_notes' => $validated['internal_notes'] ?? $order->internal_notes,
        ]);

        return back()->with('success', 'تم اعتماد الموعد النهائي وإبلاغ العميل رسمياً بموعد تسليم الطلبية.');
    }
}
