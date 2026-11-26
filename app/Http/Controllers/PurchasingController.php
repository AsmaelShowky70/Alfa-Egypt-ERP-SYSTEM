<?php

namespace App\Http\Controllers;

use App\Models\Factory;
use App\Models\FinancialTransaction;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\PurchaseRequisition;
use App\Models\RawMaterial;
use App\Models\SalesOrder;
use App\Models\StockMovement;
use App\Models\StockMovementItem;
use App\Models\Supplier;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PurchasingController extends Controller
{
    public function index(): View
    {
        $pendingRequisitions = PurchaseRequisition::with(['warehouseManager', 'salesOrder.customer', 'items.rawMaterial'])
            ->whereIn('status', ['pending_procurement', 'lead_time_quoted'])
            ->latest()
            ->get();

        $activePurchaseOrders = PurchaseOrder::with(['supplier', 'fromFactory', 'items'])
            ->whereNotIn('status', ['received_in_warehouse', 'cancelled'])
            ->latest()
            ->get();

        $suppliers = Supplier::withCount('purchaseOrders')->get();

        return view('purchasing.index', compact(
            'pendingRequisitions',
            'activePurchaseOrders',
            'suppliers'
        ));
    }

    // ================= PURCHASE REQUISITIONS (متابعة النواقص) =================
    public function requisitions(): View
    {
        $requisitions = PurchaseRequisition::with(['warehouseManager', 'salesOrder.customer', 'items.rawMaterial'])
            ->latest()
            ->paginate(15);

        return view('purchasing.requisitions.index', compact('requisitions'));
    }

    public function showRequisition(PurchaseRequisition $purchaseRequisition): View
    {
        $purchaseRequisition->load([
            'warehouseManager',
            'salesOrder.customer',
            'productionOrder',
            'items.rawMaterial',
            'purchaseOrders.supplier',
            'purchaseOrders.fromFactory',
        ]);
        $suppliers = Supplier::where('status', true)->get();
        $factories = Factory::all();

        return view('purchasing.requisitions.show', compact('purchaseRequisition', 'suppliers', 'factories'));
    }

    /**
     * Respond with lead time to Warehouse & Operations & Sales
     */
    public function quoteLeadTime(Request $request, PurchaseRequisition $purchaseRequisition): RedirectResponse
    {
        $validated = $request->validate([
            'procurement_lead_time_days' => 'required|integer|min:1',
            'estimated_arrival_date' => 'required|date',
            'procurement_reply_notes' => 'required|string',
        ]);

        $days = (int) $validated['procurement_lead_time_days'];

        $purchaseRequisition->update([
            'procurement_lead_time_days' => $days,
            'estimated_arrival_date' => $validated['estimated_arrival_date'],
            'procurement_reply_notes' => $validated['procurement_reply_notes'],
            'status' => 'lead_time_quoted',
        ]);

        // Reflect this lead time onto the related SalesOrder so Sales knows the exact date!
        if ($purchaseRequisition->salesOrder) {
            $so = $purchaseRequisition->salesOrder;
            $so->procurement_lead_time_days = $days;

            // Delivery date = today + procurement lead time + operations lead time
            $totalDays = $days + ($so->operations_lead_time_days ?? 2);
            $newDeliveryDate = Carbon::now()->addDays($totalDays);

            $so->confirmed_delivery_date = $newDeliveryDate;
            $so->internal_notes .= "\n[رد المشتريات]: توفير الخامات يستغرق {$days} أيام. الموعد النهائي المقترح للتسليم هو {$newDeliveryDate->toDateString()}";
            $so->save();
        }

        return back()->with('success', 'تم تسجيل مدة التوريد بنجاح وانعكس الموعد النهائي تلقائياً على قسم التشغيل والمبيعات لإبلاغ العميل.');
    }

    // ================= PURCHASE ORDERS (أوامر التوريد والشراء الداخلي +14%) =================
    public function purchaseOrders(): View
    {
        $orders = PurchaseOrder::with(['supplier', 'fromFactory', 'purchasingManager', 'items'])
            ->latest()
            ->paginate(15);

        return view('purchasing.orders.index', compact('orders'));
    }

    public function createPurchaseOrder(Request $request): View
    {
        $requisitionId = $request->get('requisition_id');
        $requisition = $requisitionId ? PurchaseRequisition::with('items.rawMaterial')->find($requisitionId) : null;

        $suppliers = Supplier::where('status', true)->get();
        $factories = Factory::all();
        $rawMaterials = RawMaterial::where('status', true)->get();

        return view('purchasing.orders.create', compact('requisition', 'suppliers', 'factories', 'rawMaterials'));
    }

    public function storePurchaseOrder(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'purchase_requisition_id' => 'nullable|exists:purchase_requisitions,id',
            'is_internal_factory' => 'required|boolean',
            'supplier_id' => 'nullable|required_if:is_internal_factory,0|exists:suppliers,id',
            'from_factory_id' => 'nullable|required_if:is_internal_factory,1|exists:factories,id',
            'order_date' => 'required|date',
            'expected_delivery_date' => 'required|date',
            'destination_warehouse' => 'required|string',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.raw_material_id' => 'nullable|exists:raw_materials,id',
            'items.*.item_name' => 'required|string',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        $isInternal = (bool) $validated['is_internal_factory'];
        $markupPercent = $isInternal ? 14.00 : 0.00;

        $subtotal = 0;
        foreach ($validated['items'] as $item) {
            $subtotal += ((float) $item['quantity'] * (float) $item['unit_price']);
        }

        $markupAmount = $subtotal * ($markupPercent / 100);
        $tax = $isInternal ? 0 : ($subtotal * 0.14);
        $totalAmount = $subtotal + $markupAmount + $tax;

        $prefix = $isInternal ? 'PO-INT-' : 'PO-EXT-';
        $poNumber = $prefix.date('Y').'-'.str_pad((PurchaseOrder::count() + 1), 3, '0', STR_PAD_LEFT);

        DB::beginTransaction();
        try {
            $po = PurchaseOrder::create([
                'po_number' => $poNumber,
                'purchase_requisition_id' => $validated['purchase_requisition_id'] ?? null,
                'supplier_id' => $validated['supplier_id'] ?? null,
                'is_internal_factory' => $isInternal,
                'from_factory_id' => $validated['from_factory_id'] ?? null,
                'destination_warehouse' => $validated['destination_warehouse'],
                'internal_markup_percentage' => $markupPercent,
                'status' => 'issued',
                'order_date' => $validated['order_date'],
                'expected_delivery_date' => $validated['expected_delivery_date'],
                'subtotal' => $subtotal,
                'tax' => $tax,
                'internal_markup_amount' => $markupAmount,
                'total_amount' => $totalAmount,
                'purchasing_manager_id' => Auth::id(),
                'notes' => $validated['notes'],
            ]);

            foreach ($validated['items'] as $item) {
                PurchaseOrderItem::create([
                    'purchase_order_id' => $po->id,
                    'raw_material_id' => $item['raw_material_id'] ?? null,
                    'item_name' => $item['item_name'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'subtotal' => (float) $item['quantity'] * (float) $item['unit_price'],
                    'received_quantity' => 0,
                ]);
            }

            if ($validated['purchase_requisition_id'] ?? null) {
                PurchaseRequisition::where('id', $validated['purchase_requisition_id'])->update(['status' => 'po_issued']);
            }

            // Auto-post double-entry into Financial Ledger
            if (! $isInternal && $po->supplier_id) {
                $sup = Supplier::find($po->supplier_id);
                $finTxnNumber = 'BILL-'.date('Ymd').'-'.str_pad((FinancialTransaction::count() + 1), 4, '0', STR_PAD_LEFT);
                FinancialTransaction::create([
                    'transaction_number' => $finTxnNumber,
                    'type' => 'supplier_bill',
                    'category' => 'مشتريات خامات ومستلزمات',
                    'amount' => $totalAmount,
                    'net_amount' => $subtotal,
                    'tax_vat_amount' => $tax,
                    'transaction_date' => $po->order_date,
                    'payment_method' => 'آجل',
                    'supplier_id' => $po->supplier_id,
                    'purchase_order_id' => $po->id,
                    'debit_account' => 'حـ/ مشتريات خامات ومستلزمات',
                    'credit_account' => 'حـ/ الموردين - '.($sup?->name ?? 'مورد'),
                    'description' => 'فاتورة مشتريات آلية عن أمر شراء رقم '.$po->po_number,
                    'status' => 'posted',
                    'created_by' => Auth::id(),
                ]);
            } elseif ($isInternal && $markupAmount > 0) {
                $factory = Factory::find($po->from_factory_id);
                $finTxnNumber = 'TR-14-'.date('Ymd').'-'.str_pad((FinancialTransaction::count() + 1), 4, '0', STR_PAD_LEFT);
                FinancialTransaction::create([
                    'transaction_number' => $finTxnNumber,
                    'type' => 'factory_internal_14_markup',
                    'category' => 'تسوية أرباح تشغيل داخلي 14%',
                    'amount' => $markupAmount,
                    'transaction_date' => $po->order_date,
                    'payment_method' => 'تسوية داخلية',
                    'factory_id' => $po->from_factory_id,
                    'purchase_order_id' => $po->id,
                    'debit_account' => 'حـ/ تكاليف تشغيل الكوڤير',
                    'credit_account' => 'حـ/ أرباح تشغيل - '.($factory?->name ?? 'مصنع'),
                    'description' => 'تسوية الـ 14% المحملة كربح تشغيلي للمصنع الداخلي عن أمر توريد '.$po->po_number,
                    'status' => 'posted',
                    'created_by' => Auth::id(),
                ]);
            }

            DB::commit();

            $msg = 'تم إصدار أمر الشراء رقم '.$poNumber.' بنجاح.';
            if ($isInternal) {
                $msg .= ' تم تحميل 14% تكلفة تشغيلية ('.number_format($markupAmount, 2).' ج.م) لحساب أرباح المصنع الداخلي.';
            }

            return redirect()->route('purchasing.orders.show', $po->id)->with('success', $msg);
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->with('error', 'حدث خطأ أثناء إصدار أمر الشراء: '.$e->getMessage());
        }
    }

    public function showPurchaseOrder(PurchaseOrder $purchaseOrder): View
    {
        $purchaseOrder->load(['supplier', 'fromFactory', 'purchasingManager', 'items.rawMaterial', 'purchaseRequisition']);

        return view('purchasing.orders.show', compact('purchaseOrder'));
    }

    /**
     * Receive purchase order into Central Warehouse
     */
    public function receivePurchaseOrder(Request $request, PurchaseOrder $purchaseOrder): RedirectResponse
    {
        $purchaseOrder->load('items.rawMaterial', 'supplier', 'fromFactory');

        DB::beginTransaction();
        try {
            $voucherNumber = 'VOUCH-REC-'.date('Ymd').'-'.str_pad((StockMovement::count() + 1), 3, '0', STR_PAD_LEFT);
            $type = $purchaseOrder->is_internal_factory ? 'receipt_factory_internal' : 'receipt_supplier';

            $voucher = StockMovement::create([
                'voucher_number' => $voucherNumber,
                'type' => $type,
                'reference_type' => 'purchase_order',
                'reference_id' => $purchaseOrder->id,
                'warehouse_manager_id' => Auth::id(),
                'voucher_date' => Carbon::now()->toDateString(),
                'total_value' => $purchaseOrder->total_amount,
                'received_or_delivered_by' => $purchaseOrder->is_internal_factory ? ($purchaseOrder->fromFactory->supervisor_name ?? 'مشرف المصنع الداخلي') : ($purchaseOrder->supplier->name ?? 'مندوب المورد'),
                'is_archived' => false,
                'notes' => 'استلام بموجب أمر الشراء رقم '.$purchaseOrder->po_number,
            ]);

            foreach ($purchaseOrder->items as $item) {
                $item->received_quantity = $item->quantity;
                $item->save();

                if ($item->rawMaterial) {
                    $item->rawMaterial->current_stock += (float) $item->quantity;
                    $item->rawMaterial->save();
                }

                StockMovementItem::create([
                    'stock_movement_id' => $voucher->id,
                    'item_type' => 'raw_material',
                    'item_id' => $item->raw_material_id ?? 0,
                    'item_name' => $item->item_name,
                    'unit' => $item->rawMaterial->unit ?? 'قطعة',
                    'quantity' => $item->quantity,
                    'unit_cost' => $item->unit_price,
                    'total_cost' => $item->subtotal,
                ]);
            }

            $purchaseOrder->update([
                'status' => 'received_in_warehouse',
                'actual_delivery_date' => now(),
            ]);

            // If internal factory, record the 14% markup in Financial Transactions!
            if ($purchaseOrder->is_internal_factory && $purchaseOrder->internal_markup_amount > 0) {
                FinancialTransaction::create([
                    'transaction_number' => 'FT-INT-'.time(),
                    'type' => 'factory_internal_14_markup',
                    'category' => 'تكاليف تشغيل 14%',
                    'amount' => $purchaseOrder->internal_markup_amount,
                    'transaction_date' => now(),
                    'payment_method' => 'قيد محاسبي داخلي',
                    'purchase_order_id' => $purchaseOrder->id,
                    'factory_id' => $purchaseOrder->from_factory_id,
                    'description' => 'قيد هامش ربح تشغيلي 14% لصالح '.($purchaseOrder->fromFactory->name ?? 'المصنع').' عن أمر التوريد '.$purchaseOrder->po_number,
                    'created_by' => Auth::id(),
                ]);
            }

            DB::commit();

            return redirect()->route('warehouse.vouchers.show', $voucher->id)
                ->with('success', 'تم استلام البضاعة في المخزن المركزي بنجاح وإصدار إذن الاستلام رقم '.$voucherNumber);
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->with('error', 'حدث خطأ أثناء الاستلام: '.$e->getMessage());
        }
    }

    // ================= SUPPLIERS =================
    public function suppliers(): View
    {
        $suppliers = Supplier::withCount('purchaseOrders')->get();

        return view('purchasing.suppliers.index', compact('suppliers'));
    }

    public function storeSupplier(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:suppliers,code',
            'category' => 'required|string',
            'contact_person' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'average_lead_time_days' => 'required|integer|min:1',
            'notes' => 'nullable|string',
        ]);

        Supplier::create($validated);

        return back()->with('success', 'تم إضافة المورد الجديد بنجاح.');
    }
}
