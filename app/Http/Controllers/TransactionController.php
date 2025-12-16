<?php
namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\TransactionDetail;
use App\Models\Product;
use App\Models\PriceTier;
use App\Models\Warehouse;
use App\Models\Voucher;
use App\Models\StockMutation;
use App\Models\WarehouseStock;
use App\Models\CashSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Barryvdh\DomPDF\Facade\Pdf;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $query = Transaction::with(['user', 'warehouse', 'priceTier']);

        if ($request->filled('transaction_number')) {
            $query->where('transaction_number', 'like', '%' . $request->transaction_number . '%');
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('transaction_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('transaction_date', '<=', $request->date_to);
        }

        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }

        $transactions = $query->latest('transaction_date')->paginate(20);

        return view('transactions.index', compact('transactions'));
    }

    public function show(Transaction $transaction)
    {
        $transaction->load(['details.product', 'user', 'warehouse', 'priceTier']);
        return view('transactions.show', compact('transaction'));
    }

    public function pos()
    {
        $priceTiers = PriceTier::where('status', 'active')->orderBy('priority')->get();
        $warehouses = Warehouse::where('status', 'active')->get();
        
        // Check if user has open cash session
        $openSession = CashSession::where('user_id', Auth::id())
            ->where('status', 'open')
            ->latest()
            ->first();

        // Check jika user adalah kasir dan belum buka sesi kas
        if (!$openSession && Auth::user()->role === 'kasir') {
            return redirect()->route('cash-sessions.create')
                ->with('warning', 'Silakan buka sesi kas terlebih dahulu');
        }

        return view('pos.index', compact('priceTiers', 'warehouses', 'openSession'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.qty' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
            'price_tier_id' => 'nullable|exists:price_tiers,id',
            'warehouse_id' => 'nullable|exists:warehouses,id',
            'discount_percentage' => 'nullable|numeric|min:0|max:100',
            'discount_amount' => 'nullable|numeric|min:0',
            'voucher_code' => 'nullable|string',
            'tax_percentage' => 'nullable|numeric|min:0',
            'payment_method' => 'required|in:cash,card,ewallet,transfer',
            'payment_amount' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();
        try {
            // Calculate subtotal
            $subtotal = 0;
            foreach ($request->items as $item) {
                $subtotal += $item['qty'] * $item['price'];
            }

            // Calculate discount
            $discountAmount = $request->discount_amount ?? 0;
            if ($request->filled('discount_percentage')) {
                $discountAmount = ($subtotal * $request->discount_percentage) / 100;
            }

            // Validate and apply voucher
            if ($request->filled('voucher_code')) {
                $voucher = Voucher::where('code', $request->voucher_code)->first();
                
                if (!$voucher) {
                    DB::rollback();
                    return response()->json([
                        'success' => false,
                        'message' => 'Voucher tidak ditemukan'
                    ], 400);
                }

                if (!$voucher->isValid($subtotal)) {
                    DB::rollback();
                    return response()->json([
                        'success' => false,
                        'message' => 'Voucher tidak valid atau sudah tidak berlaku'
                    ], 400);
                }

                $voucherDiscount = $voucher->calculateDiscount($subtotal);
                $discountAmount += $voucherDiscount;
                
                // Update voucher usage
                $voucher->increment('used_count');
            }

            // Calculate tax
            $taxPercentage = $request->tax_percentage ?? 0;
            $amountAfterDiscount = $subtotal - $discountAmount;
            $taxAmount = ($amountAfterDiscount * $taxPercentage) / 100;

            // Calculate total
            $total = $amountAfterDiscount + $taxAmount;

            // Validate payment
            if ($request->payment_method === 'cash' && $request->payment_amount < $total) {
                DB::rollback();
                return response()->json([
                    'success' => false,
                    'message' => 'Jumlah pembayaran tidak cukup'
                ], 400);
            }

            $changeAmount = $request->payment_method === 'cash' 
                ? $request->payment_amount - $total 
                : 0;

            // Create transaction
            $transaction = Transaction::create([
                'transaction_number' => Transaction::generateTransactionNumber(),
                'user_id' => Auth::id(),
                'warehouse_id' => $request->warehouse_id,
                'price_tier_id' => $request->price_tier_id,
                'transaction_date' => now(),
                'subtotal' => $subtotal,
                'discount_percentage' => $request->discount_percentage ?? 0,
                'discount_amount' => $discountAmount,
                'voucher_code' => $request->voucher_code,
                'tax_percentage' => $taxPercentage,
                'tax_amount' => $taxAmount,
                'total' => $total,
                'payment_method' => $request->payment_method,
                'payment_amount' => $request->payment_amount,
                'change_amount' => $changeAmount,
                'status' => 'completed',
                'notes' => $request->notes,
            ]);

            // Create transaction details and update stock
            foreach ($request->items as $item) {
                $product = Product::find($item['product_id']);

                // Validate stock
                $currentStock = $request->warehouse_id 
                    ? $product->getStockByWarehouse($request->warehouse_id)
                    : $product->stock;

                if ($currentStock < $item['qty']) {
                    DB::rollback();
                    return response()->json([
                        'success' => false,
                        'message' => "Stok {$product->name} tidak mencukupi"
                    ], 400);
                }

                // Create detail
                TransactionDetail::create([
                    'transaction_id' => $transaction->id,
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'qty' => $item['qty'],
                    'price' => $item['price'],
                    'subtotal' => $item['qty'] * $item['price'],
                    'discount' => $item['discount'] ?? 0,
                ]);

                // Update stock
                if ($request->warehouse_id) {
                    $warehouseStock = WarehouseStock::where('warehouse_id', $request->warehouse_id)
                        ->where('product_id', $product->id)
                        ->first();
                    
                    if (!$warehouseStock) {
                        DB::rollback();
                        return response()->json([
                            'success' => false,
                            'message' => "Stok produk {$product->name} tidak ditemukan di gudang"
                        ], 400);
                    }
                    
                    $oldStock = $warehouseStock->stock;
                    $warehouseStock->decrement('stock', $item['qty']);
                    
                    // Record stock mutation
                    StockMutation::create([
                        'product_id' => $product->id,
                        'warehouse_id' => $request->warehouse_id,
                        'type' => 'out',
                        'qty' => $item['qty'],
                        'stock_before' => $oldStock,
                        'stock_after' => $oldStock - $item['qty'],
                        'reference_type' => 'Transaction',
                        'reference_id' => $transaction->id,
                        'notes' => 'Penjualan: ' . $transaction->transaction_number,
                        'user_id' => Auth::id(),
                    ]);
                } else {
                    $oldStock = $product->stock;
                    $product->decrement('stock', $item['qty']);
                    
                    StockMutation::create([
                        'product_id' => $product->id,
                        'type' => 'out',
                        'qty' => $item['qty'],
                        'stock_before' => $oldStock,
                        'stock_after' => $oldStock - $item['qty'],
                        'reference_type' => 'Transaction',
                        'reference_id' => $transaction->id,
                        'notes' => 'Penjualan: ' . $transaction->transaction_number,
                        'user_id' => Auth::id(),
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Transaksi berhasil',
                'data' => [
                    'transaction_id' => $transaction->id,
                    'transaction_number' => $transaction->transaction_number,
                    'total' => $total,
                    'payment_amount' => $request->payment_amount,
                    'change_amount' => $changeAmount,
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    public function cancel(Transaction $transaction)
    {
        if ($transaction->status === 'cancelled') {
            return response()->json([
                'success' => false,
                'message' => 'Transaksi sudah dibatalkan'
            ], 400);
        }

        // Check user role - FIXED
        $userRole = Auth::user()->role;
        if ($userRole !== 'supervisor' && $userRole !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Hanya supervisor/admin yang dapat membatalkan transaksi'
            ], 403);
        }

        DB::beginTransaction();
        try {
            // Restore stock
            foreach ($transaction->details as $detail) {
                $product = $detail->product;
                
                if ($transaction->warehouse_id) {
                    $warehouseStock = WarehouseStock::where('warehouse_id', $transaction->warehouse_id)
                        ->where('product_id', $product->id)
                        ->first();
                    
                    if ($warehouseStock) {
                        $oldStock = $warehouseStock->stock;
                        $warehouseStock->increment('stock', $detail->qty);
                        
                        StockMutation::create([
                            'product_id' => $product->id,
                            'warehouse_id' => $transaction->warehouse_id,
                            'type' => 'in',
                            'qty' => $detail->qty,
                            'stock_before' => $oldStock,
                            'stock_after' => $oldStock + $detail->qty,
                            'reference_type' => 'Transaction',
                            'reference_id' => $transaction->id,
                            'notes' => 'Pembatalan transaksi: ' . $transaction->transaction_number,
                            'user_id' => Auth::id(),
                        ]);
                    }
                } else {
                    $oldStock = $product->stock;
                    $product->increment('stock', $detail->qty);
                    
                    StockMutation::create([
                        'product_id' => $product->id,
                        'type' => 'in',
                        'qty' => $detail->qty,
                        'stock_before' => $oldStock,
                        'stock_after' => $oldStock + $detail->qty,
                        'reference_type' => 'Transaction',
                        'reference_id' => $transaction->id,
                        'notes' => 'Pembatalan transaksi: ' . $transaction->transaction_number,
                        'user_id' => Auth::id(),
                    ]);
                }
            }

            // Update voucher if used
            if ($transaction->voucher_code) {
                $voucher = Voucher::where('code', $transaction->voucher_code)->first();
                if ($voucher) {
                    $voucher->decrement('used_count');
                }
            }

            $transaction->update(['status' => 'cancelled']);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Transaksi berhasil dibatalkan'
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    public function printReceipt(Transaction $transaction)
    {
        $transaction->load(['details.product', 'user', 'warehouse']);
        
        $pdf = Pdf::loadView('transactions.receipt', compact('transaction'));
        return $pdf->stream('receipt-' . $transaction->transaction_number . '.pdf');
    }

    public function validateVoucher(Request $request)
    {
        $voucher = Voucher::where('code', $request->code)->first();

        if (!$voucher) {
            return response()->json([
                'success' => false,
                'message' => 'Voucher tidak ditemukan'
            ], 404);
        }

        $subtotal = $request->subtotal ?? 0;

        if (!$voucher->isValid($subtotal)) {
            return response()->json([
                'success' => false,
                'message' => 'Voucher tidak valid atau sudah tidak berlaku'
            ], 400);
        }

        $discount = $voucher->calculateDiscount($subtotal);

        return response()->json([
            'success' => true,
            'data' => [
                'voucher' => $voucher,
                'discount' => $discount,
            ]
        ]);
    }
}