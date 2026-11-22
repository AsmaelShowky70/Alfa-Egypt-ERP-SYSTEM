<?php

namespace App\Http\Controllers;

use App\Models\DailyInventoryCheck;
use App\Models\ProductionOrder;
use App\Models\PurchaseOrder;
use App\Models\SalesOrder;
use App\Models\StockMovement;
use Illuminate\View\View;

class PrintController extends Controller
{
    public function voucher(StockMovement $voucher): View
    {
        $voucher->load(['warehouseManager', 'items']);
        return view('print.voucher', compact('voucher'));
    }

    public function workOrder(ProductionOrder $productionOrder): View
    {
        $productionOrder->load([
            'factory.machines',
            'product.boms.rawMaterial',
            'machine',
            'salesOrder.customer',
            'operationsManager'
        ]);
        return view('print.work_order', compact('productionOrder'));
    }

    public function purchaseOrder(PurchaseOrder $purchaseOrder): View
    {
        $purchaseOrder->load([
            'supplier',
            'fromFactory',
            'purchasingManager',
            'items.rawMaterial'
        ]);
        return view('print.purchase_order', compact('purchaseOrder'));
    }

    public function dailyInventoryCheck(DailyInventoryCheck $dailyCheck): View
    {
        $dailyCheck->load(['warehouseManager', 'items']);
        return view('print.daily_inventory_check', compact('dailyCheck'));
    }

    public function salesInvoice(SalesOrder $order): View
    {
        $order->load(['customer', 'salesRep', 'items.product']);
        return view('print.sales_invoice', compact('order'));
    }
}
