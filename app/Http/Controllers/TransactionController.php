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
    /* =========================
     | LIST TRANSAKSI
     ========================= */
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

    /* =========================
     | DETAIL TRANSAKSI
     ========================= */
    public function show(Transaction $transaction)
    {
        $transaction->load(['details.product', 'user', 'warehouse', 'priceTier']);
        return view('transactions.show', compact('transaction'));
    }

    /* =========================
     | POS PAGE
     ========================= */
    public function pos()
    {
        $priceTiers = PriceTier::where('status', 'active')->orderBy('priority')->get();
        $warehouses = Warehouse::where('status', 'active')->get();

        $openSession = CashSession::where('user_id', Auth::id())
            ->where('status', 'open')
            ->latest()
            ->first();

        if (!$openSession && Auth::user()->role === 'kasir') {
            return redirect()->route('cash-sessions.create')
                ->with('warning', 'Silakan buka sesi kas terlebih dahulu');
        }

        return view('pos.index', compact('priceTiers', 'warehouses', 'openSession'));
    }

    /* =========================
     | SIMPAN TRANSAKSI (POS)
     ========================= */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
            'warehouse_id' => 'nullable|exists:warehouses,id',
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
            $subtotal = collect($request->items)
                ->sum(fn ($i) => $i['quantity'] * $i['price']);

            $total = $subtotal;

            if ($request->payment_method === 'cash' && $request->payment_amount < $total) {
                throw new \Exception('Pembayaran tidak cukup');
            }

            $changeAmount = $request->payment_method === 'cash'
                ? $request->payment_amount - $total
                : 0;

            $stockOverride = false;

            $transaction = Transaction::create([
                'transaction_number' => Transaction::generateTransactionNumber(),
                'user_id' => Auth::id(),
                'warehouse_id' => $request->warehouse_id,
                'transaction_date' => now(),
                'subtotal' => $subtotal,
                'total' => $total,
                'payment_method' => $request->payment_method,
                'payment_amount' => $request->payment_amount,
                'change_amount' => $changeAmount,
                'status' => 'completed',
                'notes' => $request->notes,
                'stock_override' => false,
            ]);

            foreach ($request->items as $item) {
                $product = Product::lockForUpdate()->find($item['product_id']);
                $qty = $item['quantity'];

                // ===== VALIDASI STOK =====
                if ($request->warehouse_id) {
                    $stock = WarehouseStock::where('warehouse_id', $request->warehouse_id)
                        ->where('product_id', $product->id)
                        ->lockForUpdate()
                        ->first();

                    $available = $stock?->stock ?? 0;
                } else {
                    $available = $product->stock;
                }

                if ($available < $qty) {
                    if (!Auth::user()->hasRole(['owner', 'supervisor'])) {
                        throw new \Exception("Stok {$product->name} tidak mencukupi");
                    }

                    // override stok
                    $stockOverride = true;
                }

                TransactionDetail::create([
                    'transaction_id' => $transaction->id,
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'quantity' => $qty,
                    'price' => $item['price'],
                    'subtotal' => $qty * $item['price'],
                ]);

                // ===== KURANGI STOK =====
                if ($request->warehouse_id && isset($stock)) {
                    $before = $stock->stock;
                    $stock->decrement('stock', $qty);

                    StockMutation::create([
                        'product_id' => $product->id,
                        'warehouse_id' => $request->warehouse_id,
                        'type' => 'out',
                        'quantity' => $qty,
                        'stock_before' => $before,
                        'stock_after' => $before - $qty,
                        'reference_type' => 'Transaction',
                        'reference_id' => $transaction->id,
                        'user_id' => Auth::id(),
                    ]);
                } else {
                    $before = $product->stock;
                    $product->decrement('stock', $qty);

                    StockMutation::create([
                        'product_id' => $product->id,
                        'type' => 'out',
                        'quantity' => $qty,
                        'stock_before' => $before,
                        'stock_after' => $before - $qty,
                        'reference_type' => 'Transaction',
                        'reference_id' => $transaction->id,
                        'user_id' => Auth::id(),
                    ]);
                }
            }

            if ($stockOverride) {
                $transaction->update([
                    'stock_override' => true,
                    'approved_by' => Auth::id(),
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Transaksi berhasil',
                'data' => $transaction
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /* =========================
     | BATALKAN TRANSAKSI
     ========================= */
    public function cancel(Transaction $transaction)
{
    $user = Auth::user();

    // ❌ Jika sudah dibatalkan
    if ($transaction->status === 'cancelled') {
        return back()->with('error', 'Transaksi sudah dibatalkan');
    }

    // 📅 Cek apakah transaksi hari ini
    $isToday = $transaction->transaction_date->isToday();

    if (!$isToday && !$user->hasRole('owner')) {
        abort(403, 'Transaksi hari sebelumnya hanya bisa dibatalkan oleh OWNER');
    }

    if ($isToday && !$user->hasRole(['owner', 'supervisor'])) {
        abort(403, 'Anda tidak memiliki izin membatalkan transaksi');
    }

    DB::beginTransaction();
    try {
        foreach ($transaction->details as $detail) {
            $product = $detail->product;

            $before = $product->stock;
            $product->increment('stock', $detail->quantity);

            // (Opsional tapi disarankan)
            StockMutation::create([
                'product_id'     => $product->id,
                'type'           => 'in',
                'quantity'       => $detail->quantity,
                'stock_before'   => $before,
                'stock_after'    => $before + $detail->quantity,
                'reference_type' => 'Transaction',
                'reference_id'   => $transaction->id,
                'notes'          => 'Pembatalan transaksi',
                'user_id'        => $user->id,
            ]);
        }

        $transaction->update([
            'status' => 'cancelled'
        ]);

        DB::commit();

        return back()->with('success', 'Transaksi berhasil dibatalkan');
    } catch (\Exception $e) {
        DB::rollBack();
        return back()->with('error', 'Gagal membatalkan transaksi: ' . $e->getMessage());
    }
}

    /* =========================
     | CETAK STRUK
     ========================= */
    public function printReceipt(Transaction $transaction)
    {
        $transaction->load(['details.product', 'user', 'warehouse']);
        $pdf = Pdf::loadView('transactions.receipt', compact('transaction'));
        return $pdf->stream('receipt-' . $transaction->transaction_number . '.pdf');
    }
}
