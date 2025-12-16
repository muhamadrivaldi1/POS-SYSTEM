<?php

namespace App\Http\Controllers;

use App\Models\ReturnModel;
use App\Models\ReturnDetail;
use App\Models\Transaction;
use App\Models\StockMutation;
use App\Models\WarehouseStock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ReturnController extends Controller
{
    public function index(Request $request)
    {
        $query = ReturnModel::with(['transaction', 'user', 'approver']);

        if ($request->filled('return_number')) {
            $query->where('return_number', 'like', '%' . $request->return_number . '%');
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $returns = $query->latest('return_date')->paginate(20);

        return view('returns.index', compact('returns'));
    }

    public function create(Transaction $transaction)
    {
        return view('returns.create', compact('transaction'));
    }

    public function searchTransaction(Request $request)
    {
        $transaction = Transaction::with(['details.product'])
            ->where('transaction_number', $request->transaction_number)
            ->where('status', 'completed')
            ->first();

        if (!$transaction) {
            return response()->json([
                'success' => false,
                'message' => 'Transaksi tidak ditemukan atau sudah dibatalkan'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $transaction
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'transaction_id' => 'required|exists:transactions,id',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.qty' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
            'reason' => 'required|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        DB::beginTransaction();
        try {
            $transaction = Transaction::find($request->transaction_id);

            $total = 0;
            foreach ($request->items as $item) {
                $total += $item['qty'] * $item['price'];
            }

            $return = ReturnModel::create([
                'return_number' => ReturnModel::generateReturnNumber(),
                'transaction_id' => $transaction->id,
                'user_id' => Auth::id(),
                'return_date' => now(),
                'total' => $total,
                'status' => 'pending',
                'reason' => $request->reason,
            ]);

            foreach ($request->items as $item) {
                ReturnDetail::create([
                    'return_id' => $return->id,
                    'product_id' => $item['product_id'],
                    'qty' => $item['qty'],
                    'price' => $item['price'],
                    'subtotal' => $item['qty'] * $item['price'],
                ]);
            }

            DB::commit();

            return redirect()->route('returns.index')
                ->with('success', 'Return berhasil dibuat, menunggu persetujuan supervisor');

        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function approve(ReturnModel $return)
    {
        $userRole = Auth::user()->role;
        if ($userRole !== 'supervisor' && $userRole !== 'admin') {
            return redirect()->back()
                ->with('error', 'Hanya supervisor/admin yang dapat menyetujui return');
        }

        if ($return->status !== 'pending') {
            return redirect()->back()
                ->with('error', 'Return sudah diproses');
        }

        DB::beginTransaction();
        try {
            $transaction = $return->transaction;

            // Restore stock
            foreach ($return->details as $detail) {
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
                            'reference_type' => 'Return',
                            'reference_id' => $return->id,
                            'notes' => 'Return: ' . $return->return_number,
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
                        'reference_type' => 'Return',
                        'reference_id' => $return->id,
                        'notes' => 'Return: ' . $return->return_number,
                        'user_id' => Auth::id(),
                    ]);
                }
            }

            $return->update([
                'status' => 'approved',
                'approved_by' => Auth::id(),
            ]);

            DB::commit();

            return redirect()->route('returns.index')
                ->with('success', 'Return berhasil disetujui');

        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function reject(ReturnModel $return)
    {
        $userRole = Auth::user()->role;
        if ($userRole !== 'supervisor' && $userRole !== 'admin') {
            return redirect()->back()
                ->with('error', 'Hanya supervisor/admin yang dapat menolak return');
        }

        if ($return->status !== 'pending') {
            return redirect()->back()
                ->with('error', 'Return sudah diproses');
        }

        $return->update([
            'status' => 'rejected',
            'approved_by' => Auth::id(),
        ]);

        return redirect()->route('returns.index')
            ->with('success', 'Return ditolak');
    }
}
