<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use App\Models\PurchaseDetail;
use App\Models\Product;
use App\Models\Warehouse;
use App\Models\StockMutation;
use App\Models\WarehouseStock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PurchaseController extends Controller
{
    public function index(Request $request)
    {
        $query = Purchase::with(['user', 'warehouse']);

        if ($request->filled('purchase_number')) {
            $query->where('purchase_number', 'like', '%' . $request->purchase_number . '%');
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('purchase_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('purchase_date', '<=', $request->date_to);
        }

        $purchases = $query->latest('purchase_date')->paginate(20);

        return view('purchases.index', compact('purchases'));
    }

    public function create()
    {
        $warehouses = Warehouse::where('status', 'active')->get();
        $products = Product::where('status', 'active')->get();

        return view('purchases.create', compact('warehouses', 'products'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'supplier_name' => 'required|string|max:255',
            'warehouse_id' => 'nullable|exists:warehouses,id',
            'purchase_date' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.qty' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        DB::beginTransaction();
        try {
            $total = 0;
            foreach ($request->items as $item) {
                $total += $item['qty'] * $item['price'];
            }

            $purchase = Purchase::create([
                'purchase_number' => Purchase::generatePurchaseNumber(),
                'user_id' => auth()->id(),
                'warehouse_id' => $request->warehouse_id,
                'supplier_name' => $request->supplier_name,
                'purchase_date' => $request->purchase_date,
                'total' => $total,
                'status' => 'completed',
                'notes' => $request->notes,
            ]);

            foreach ($request->items as $item) {
                $product = Product::find($item['product_id']);

                PurchaseDetail::create([
                    'purchase_id' => $purchase->id,
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'qty' => $item['qty'],
                    'price' => $item['price'],
                    'subtotal' => $item['qty'] * $item['price'],
                ]);

                // Update stock
                if ($request->warehouse_id) {
                    $warehouseStock = WarehouseStock::firstOrCreate(
                        [
                            'warehouse_id' => $request->warehouse_id,
                            'product_id' => $product->id,
                        ],
                        ['stock' => 0]
                    );

                    $oldStock = $warehouseStock->stock;
                    $warehouseStock->increment('stock', $item['qty']);

                    StockMutation::create([
                        'product_id' => $product->id,
                        'warehouse_id' => $request->warehouse_id,
                        'type' => 'in',
                        'qty' => $item['qty'],
                        'stock_before' => $oldStock,
                        'stock_after' => $oldStock + $item['qty'],
                        'reference_type' => 'Purchase',
                        'reference_id' => $purchase->id,
                        'notes' => 'Pembelian: ' . $purchase->purchase_number,
                        'user_id' => auth()->id(),
                    ]);
                } else {
                    $oldStock = $product->stock;
                    $product->increment('stock', $item['qty']);

                    StockMutation::create([
                        'product_id' => $product->id,
                        'type' => 'in',
                        'qty' => $item['qty'],
                        'stock_before' => $oldStock,
                        'stock_after' => $oldStock + $item['qty'],
                        'reference_type' => 'Purchase',
                        'reference_id' => $purchase->id,
                        'notes' => 'Pembelian: ' . $purchase->purchase_number,
                        'user_id' => auth()->id(),
                    ]);
                }
            }

            DB::commit();

            return redirect()->route('purchases.index')
                ->with('success', 'Pembelian berhasil disimpan');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function show(Purchase $purchase)
    {
        $purchase->load(['details.product', 'user', 'warehouse']);
        return view('purchases.show', compact('purchase'));
    }

    public function edit(Purchase $purchase)
    {
        // optional: batasi edit hanya jika status tertentu
        if ($purchase->status !== 'draft') {
            return redirect()
                ->route('purchases.index')
                ->with('error', 'Pembelian tidak dapat diedit');
        }

        return view('purchases.edit', compact('purchase'));
    }
}
