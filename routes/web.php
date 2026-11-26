<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FactoryController;
use App\Http\Controllers\FinanceController;
use App\Http\Controllers\MaintenanceController;
use App\Http\Controllers\OperationsController;
use App\Http\Controllers\PrintController;
use App\Http\Controllers\PurchasingController;
use App\Http\Controllers\SalesController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WarehouseController;
use Illuminate\Support\Facades\Route;

// Authentication Routes
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::match(['get', 'post'], '/login/fast/{user?}', [AuthController::class, 'fastLogin'])->name('login.fast');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware('auth')->group(function () {
    // Universal Dashboard & Profile
    Route::get('/', [DashboardController::class, 'index'])->name('home');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/profile', [AuthController::class, 'profile'])->name('profile');
    Route::post('/profile', [AuthController::class, 'updateProfile'])->name('profile.update');

    // 1. Sales & CRM Department
    Route::middleware('role:sales,owner,gm')->prefix('sales')->name('sales.')->group(function () {
        Route::get('/customers', [SalesController::class, 'customers'])->name('customers');
        Route::get('/customers/create', [SalesController::class, 'createCustomer'])->name('customers.create');
        Route::post('/customers', [SalesController::class, 'storeCustomer'])->name('customers.store');
        Route::get('/customers/{customer}', [SalesController::class, 'showCustomer'])->name('customers.show');
        Route::get('/customers/{customer}/edit', [SalesController::class, 'editCustomer'])->name('customers.edit');
        Route::put('/customers/{customer}', [SalesController::class, 'updateCustomer'])->name('customers.update');
        Route::delete('/customers/{customer}', [SalesController::class, 'destroyCustomer'])->name('customers.destroy');
        Route::post('/customers/{customer}/custom-product', [SalesController::class, 'storeCustomProduct'])->name('customers.custom_product');

        Route::get('/orders', [SalesController::class, 'orders'])->name('orders');
        Route::get('/orders/create', [SalesController::class, 'createOrder'])->name('orders.create');
        Route::post('/orders', [SalesController::class, 'storeOrder'])->name('orders.store');
        Route::get('/orders/{order}', [SalesController::class, 'showOrder'])->name('orders.show');
        Route::post('/orders/{order}/confirm-delivery', [SalesController::class, 'confirmDeliveryDateToCustomer'])->name('orders.confirm_delivery');
    });

    // 2. Operations & Production Department
    Route::middleware('role:operations,maintenance,owner,gm')->prefix('operations')->name('operations.')->group(function () {
        Route::get('/hourly-reports', [OperationsController::class, 'hourlyReports'])->name('hourlyReports');
        Route::get('/hourly-reports/export', [OperationsController::class, 'hourlyReports'])->name('hourlyReports.export');
        Route::post('/hourly-reports', [OperationsController::class, 'storeHourlyReport'])->name('hourlyReports.store');
        Route::put('/hourly-reports/{hourlyMachineReport}', [OperationsController::class, 'updateHourlyReport'])->name('hourlyReports.update');
        Route::post('/hourly-reports/{hourlyMachineReport}/update', [OperationsController::class, 'updateHourlyReport'])->name('hourlyReports.updatePost');
    });

    Route::middleware('role:operations,owner,gm')->prefix('operations')->name('operations.')->group(function () {
        Route::get('/', [OperationsController::class, 'index'])->name('index');
        Route::post('/schedule/{order}', [OperationsController::class, 'scheduleOrder'])->name('schedule');
        Route::get('/work-orders', [OperationsController::class, 'workOrders'])->name('workOrders');
        Route::get('/work-orders/{productionOrder}', [OperationsController::class, 'showWorkOrder'])->name('workOrders.show');
        Route::post('/work-orders/{productionOrder}/status', [OperationsController::class, 'updateWorkOrderStatus'])->name('workOrders.status');
        Route::get('/requisitions', [OperationsController::class, 'requisitions'])->name('requisitions');
        Route::post('/requisitions', [OperationsController::class, 'storeRequisition'])->name('requisitions.store');
        Route::post('/requisitions/{materialRequisition}/confirm-receipt', [OperationsController::class, 'confirmReceipt'])->name('requisitions.confirmReceipt');
    });

    // 3. Central Warehouse Department
    Route::middleware('role:warehouse,maintenance,owner,gm')->prefix('warehouse')->name('warehouse.')->group(function () {
        Route::get('/spare-parts', [WarehouseController::class, 'spareParts'])->name('spareParts');
    });

    Route::middleware('role:warehouse,owner,gm')->prefix('warehouse')->name('warehouse.')->group(function () {
        Route::get('/', [WarehouseController::class, 'index'])->name('index');
        Route::get('/raw-materials', [WarehouseController::class, 'rawMaterials'])->name('rawMaterials');
        Route::get('/finished-goods', [WarehouseController::class, 'finishedGoods'])->name('finishedGoods');

        Route::get('/material-requisitions', [WarehouseController::class, 'materialRequisitions'])->name('materialRequisitions');
        Route::get('/material-requisitions/{materialRequisition}', [WarehouseController::class, 'showMaterialRequisition'])->name('materialRequisitions.show');
        Route::post('/material-requisitions/{materialRequisition}/issue', [WarehouseController::class, 'issueRequisition'])->name('materialRequisitions.issue');
        Route::post('/material-requisitions/{materialRequisition}/purchase-missing', [WarehouseController::class, 'createPurchaseRequisitionFromMissing'])->name('materialRequisitions.purchaseMissing');

        Route::get('/vouchers', [WarehouseController::class, 'vouchers'])->name('vouchers');
        Route::get('/vouchers/create', [WarehouseController::class, 'createVoucher'])->name('vouchers.create');
        Route::post('/vouchers', [WarehouseController::class, 'storeVoucher'])->name('vouchers.store');
        Route::get('/vouchers/{voucher}', [WarehouseController::class, 'showVoucher'])->name('vouchers.show');

        Route::get('/daily-checks', [WarehouseController::class, 'dailyChecks'])->name('dailyChecks');
        Route::get('/daily-checks/create', [WarehouseController::class, 'createDailyCheck'])->name('dailyChecks.create');
        Route::post('/daily-checks', [WarehouseController::class, 'storeDailyCheck'])->name('dailyChecks.store');
        Route::get('/daily-checks/{dailyCheck}', [WarehouseController::class, 'showDailyCheck'])->name('dailyChecks.show');

        // Warehouse Reports, PDF Printing & Archival System
        Route::get('/reports', [WarehouseController::class, 'reports'])->name('reports');
        Route::get('/reports/print', [WarehouseController::class, 'printReport'])->name('reports.print');
        Route::post('/reports/archive', [WarehouseController::class, 'archiveReport'])->name('reports.archive');
        Route::get('/reports/archived', [WarehouseController::class, 'archivedReports'])->name('reports.archived');
        Route::get('/reports/archived/{archivedReport}', [WarehouseController::class, 'showArchivedReport'])->name('reports.showArchived');
        Route::post('/factories', [WarehouseController::class, 'storeFactory'])->name('factories.store');
    });

    // 4. Purchasing & Supply Chain Department
    Route::middleware('role:purchasing,owner,gm')->prefix('purchasing')->name('purchasing.')->group(function () {
        Route::get('/', [PurchasingController::class, 'index'])->name('index');
        Route::get('/requisitions', [PurchasingController::class, 'requisitions'])->name('requisitions');
        Route::get('/requisitions/{purchaseRequisition}', [PurchasingController::class, 'showRequisition'])->name('requisitions.show');
        Route::post('/requisitions/{purchaseRequisition}/lead-time', [PurchasingController::class, 'quoteLeadTime'])->name('requisitions.leadTime');

        Route::get('/orders', [PurchasingController::class, 'purchaseOrders'])->name('orders');
        Route::get('/orders/create', [PurchasingController::class, 'createPurchaseOrder'])->name('orders.create');
        Route::post('/orders', [PurchasingController::class, 'storePurchaseOrder'])->name('orders.store');
        Route::get('/orders/{purchaseOrder}', [PurchasingController::class, 'showPurchaseOrder'])->name('orders.show');
        Route::post('/orders/{purchaseOrder}/receive', [PurchasingController::class, 'receivePurchaseOrder'])->name('orders.receive');

        Route::get('/suppliers', [PurchasingController::class, 'suppliers'])->name('suppliers');
        Route::post('/suppliers', [PurchasingController::class, 'storeSupplier'])->name('suppliers.store');
    });

    // 5. Finance, Accounts & Cost Centers Department
    Route::middleware('role:finance,sales,operations,owner,gm')->prefix('finance')->name('finance.')->group(function () {
        Route::get('/loss-alarms', [FinanceController::class, 'lossAlarms'])->name('lossAlarms');
    });

    Route::middleware('role:finance,owner,gm')->prefix('finance')->name('finance.')->group(function () {
        Route::get('/', [FinanceController::class, 'index'])->name('index');
        Route::get('/transactions/create', [FinanceController::class, 'createTransaction'])->name('transactions.create');
        Route::post('/transactions', [FinanceController::class, 'storeTransaction'])->name('transactions.store');

        // Customer Accounts & Receivables (شجرة ومستحقات العملاء)
        Route::get('/customers', [FinanceController::class, 'customers'])->name('customers');
        Route::get('/customers/{customer}/statement', [FinanceController::class, 'customerStatement'])->name('customers.statement');
        Route::post('/customers/{customer}/payment', [FinanceController::class, 'storeCustomerPayment'])->name('customers.payment');

        // Supplier Accounts & Payables & Advances (حسابات الموردين والعربونات)
        Route::get('/suppliers', [FinanceController::class, 'suppliers'])->name('suppliers');
        Route::get('/suppliers/{supplier}/statement', [FinanceController::class, 'supplierStatement'])->name('suppliers.statement');
        Route::post('/suppliers/{supplier}/payment', [FinanceController::class, 'storeSupplierPayment'])->name('suppliers.payment');

        // Treasury & Cash Flow Tracker (الخزينة والسيولة النقدية)
        Route::get('/treasury', [FinanceController::class, 'treasury'])->name('treasury');
        Route::post('/treasury/voucher', [FinanceController::class, 'storeTreasuryVoucher'])->name('treasury.voucher');

        // General & Operating Expenses (المصروفات العمومية والتشغيلية)
        Route::get('/expenses', [FinanceController::class, 'expenses'])->name('expenses');
        Route::post('/expenses', [FinanceController::class, 'storeExpense'])->name('expenses.store');

        // General Journal Double-Entry Ledger (دفتر اليومية العامة والقيود المزدوجة)
        Route::get('/journal', [FinanceController::class, 'journal'])->name('journal');
        Route::post('/journal', [FinanceController::class, 'storeManualJournal'])->name('journal.store');

        // Monthly Financial Analysis (التحليل المالي الشهري لشهر أكتوبر وسبتمبر)
        Route::get('/analysis/october', [FinanceController::class, 'octoberAnalysis'])->name('analysis.october');

        Route::get('/pricing', [FinanceController::class, 'pricing'])->name('pricing');
        Route::post('/pricing/{product}', [FinanceController::class, 'updatePrice'])->name('pricing.update');
        Route::post('/pricing/{product}/recalculate', [FinanceController::class, 'recalculateProductCost'])->name('pricing.recalculate');
    });

    // 6. Independent Factories & Production Lines (Cost & Profit Centers)
    Route::middleware('role:operations,finance,maintenance,sales,purchasing,owner,gm')->prefix('factories')->name('factories.')->group(function () {
        Route::get('/', [FactoryController::class, 'index'])->name('index');
        Route::get('/{factory}', [FactoryController::class, 'show'])->name('show');
    });

    // 7. Maintenance & Engineering Department
    Route::middleware('role:maintenance,operations,owner,gm')->prefix('maintenance')->name('maintenance.')->group(function () {
        Route::get('/', [MaintenanceController::class, 'index'])->name('index');
        Route::post('/machines/store', [MaintenanceController::class, 'storeMachine'])->name('machines.store');
        Route::post('/machines/{machine}/status', [MaintenanceController::class, 'updateMachineStatus'])->name('machines.status');
    });

    // 8. User & Access Control (Top Management: Owner & General Manager only)
    Route::middleware('role:owner,gm')->prefix('users')->name('users.')->group(function () {
        Route::get('/', [UserController::class, 'index'])->name('index');
        Route::get('/create', [UserController::class, 'create'])->name('create');
        Route::post('/', [UserController::class, 'store'])->name('store');
        Route::get('/{user}/edit', [UserController::class, 'edit'])->name('edit');
        Route::put('/{user}', [UserController::class, 'update'])->name('update');
        Route::post('/{user}/reset-password', [UserController::class, 'resetPassword'])->name('resetPassword');
    });

    // 9. Printable Vouchers & Archiving
    Route::prefix('print')->name('print.')->group(function () {
        Route::get('/voucher/{voucher}', [PrintController::class, 'voucher'])->name('voucher');
        Route::get('/work-order/{productionOrder}', [PrintController::class, 'workOrder'])->name('workOrder');
        Route::get('/purchase-order/{purchaseOrder}', [PrintController::class, 'purchaseOrder'])->name('purchaseOrder');
        Route::get('/daily-check/{dailyCheck}', [PrintController::class, 'dailyInventoryCheck'])->name('dailyCheck');
        Route::get('/sales-invoice/{order}', [PrintController::class, 'salesInvoice'])->name('salesInvoice');
    });
});
