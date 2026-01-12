<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\PriceTierController;
use App\Http\Controllers\WarehouseController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\ReturnController;
use App\Http\Controllers\CashSessionController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\PosController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Authentication Routes
Route::get('/', fn() => redirect()->route('login'));

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.process');
Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('ensure.cash.session.closed')
    ->name('logout'); // gabungkan logout + middleware

// Protected Routes
Route::middleware('auth')->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Admin Only
    Route::middleware('role:admin')->group(function () {
        Route::resource('users', UserController::class);
    });

    // Admin & Supervisor
    Route::middleware('role:admin,supervisor')->group(function () {
        // Master Data
        Route::resources([
            'categories' => CategoryController::class,
            'price-tiers' => PriceTierController::class,
            'warehouses' => WarehouseController::class,
            'products' => ProductController::class,
        ]);

        // Product Barcode
        Route::post('/products/{product}/barcodes', [ProductController::class, 'addBarcode'])->name('products.barcodes.add');
        Route::delete('/products/barcodes/{barcode}', [ProductController::class, 'deleteBarcode'])->name('products.barcodes.delete');

        // Purchases
        Route::resource('purchases', PurchaseController::class)->except(['edit', 'update', 'destroy']);
        Route::get('/purchases/{purchase}/edit', [PurchaseController::class, 'edit'])->name('purchases.edit');
        Route::put('/purchases/{purchase}', [PurchaseController::class, 'update'])->name('purchases.update');

        // Cancel Transaction
        Route::post('/transactions/{transaction}/cancel', [TransactionController::class, 'cancel'])->name('transactions.cancel');

        // Reports
        Route::get('/reports/sales', [ReportController::class, 'sales'])->name('reports.sales');
        Route::get('/reports/stock', [ReportController::class, 'stock'])->name('reports.stock');
        Route::get('/reports/best-selling', [ReportController::class, 'bestSelling'])->name('reports.best-selling');

        // Return approve/reject
        Route::post('/returns/{return}/approve', [ReturnController::class, 'approve'])->name('returns.approve');
        Route::post('/returns/{return}/reject', [ReturnController::class, 'reject'])->name('returns.reject');
    });

    // All Authenticated Users
    // Product Search (POS & General)
    Route::get('/products/search/barcode', [ProductController::class, 'searchByBarcode'])->name('products.search.barcode');
    Route::get('/products/search/name', [ProductController::class, 'searchByName'])->name('products.search.name');

    // POS
    Route::get('/pos', [TransactionController::class, 'pos'])->name('pos.index');
    Route::prefix('pos/products')->group(function () {
        Route::get('search/barcode', [PosController::class, 'searchByBarcode'])->name('pos.products.search.barcode');
        Route::get('search/name', [PosController::class, 'searchByName'])->name('pos.products.search.name');
    });
    
    // Transactions
    Route::post('/transactions', [TransactionController::class, 'store'])->name('transactions.store');
    Route::get('/transactions', [TransactionController::class, 'index'])->name('transactions.index');
    Route::get('/transactions/{transaction}', [TransactionController::class, 'show'])->name('transactions.show');
    Route::get('/transactions/{transaction}/print', [TransactionController::class, 'printReceipt'])->name('transactions.print');

    // Validate Voucher
    Route::post('/api/vouchers/validate', [TransactionController::class, 'validateVoucher'])->name('vouchers.validate');

    // Returns
    Route::get('returns', [ReturnController::class, 'index'])->name('returns.index');
    Route::get('returns/create/{transaction}', [ReturnController::class, 'create'])->name('returns.create');
    Route::post('returns', [ReturnController::class, 'store'])->name('returns.store');
    Route::post('returns/search-transaction', [ReturnController::class, 'searchTransaction'])->name('returns.searchTransaction');

    // Cash Sessions
    Route::get('/cash-sessions', [CashSessionController::class, 'index'])->name('cash-sessions.index');
    Route::get('/cash-sessions/create', [CashSessionController::class, 'create'])->name('cash-sessions.create');
    Route::post('/cash-sessions', [CashSessionController::class, 'store'])->name('cash-sessions.store');
    Route::get('/cash-sessions/{cashSession}', [CashSessionController::class, 'show'])->name('cash-sessions.show');
    Route::post('/cash-sessions/{cashSession}/close', [CashSessionController::class, 'close'])->name('cash-sessions.close');
});
