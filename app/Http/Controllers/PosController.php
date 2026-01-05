<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Warehouse;
use App\Models\PriceTier;
use App\Models\CashSession;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;

class PosController extends Controller
{
    /**
     * Halaman POS
     */
    public function index()
    {
        $warehouses = Warehouse::where('status', 'active')
            ->orderByRaw('FIELD(name, "Gudang Cabang", "Gudang Utama")')
            ->get();

        $priceTiers = PriceTier::where('status', 'active')->get();

        $openSession = CashSession::where('user_id', Auth::id())
            ->where('status', 'open')
            ->latest()
            ->first();

        if (Auth::user()->role === 'kasir' && !$openSession) {
            return redirect()
                ->route('cash-sessions.create')
                ->with('warning', 'Silakan buka sesi kas terlebih dahulu sebelum menggunakan POS');
        }

        return view('pos.index', compact('warehouses', 'priceTiers', 'openSession'));
    }

    /**
     * Scan / Cari produk via barcode
     */
    public function searchByBarcode(Request $request)
    {
        $warehouse_id = $request->warehouse_id;
        $barcode = $request->barcode;

        $product = Product::where('code', $barcode)->first();

        if (!$product) {
            return response()->json(['success' => false, 'message' => 'Produk tidak ditemukan']);
        }

        // ambil stok sesuai gudang menggunakan helper
        $productStock = $product->getStockByWarehouse($warehouse_id);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $product->id,
                'code' => $product->code,
                'name' => $product->name,
                'base_price' => $product->base_price,
                'stock' => $productStock,
            ]
        ]);
    }

    /**
     * Cari produk berdasarkan nama / kode
     */
    public function searchByName(Request $request)
    {
        $warehouse_id = $request->warehouse_id;
        $keyword = $request->keyword;

        $products = Product::where('name', 'like', "%$keyword%")
            ->orWhere('code', 'like', "%$keyword%")
            ->get()
            ->map(function ($product) use ($warehouse_id) {
                // Ambil stok dari warehouse_stocks sesuai warehouse_id
                $productStock = $product->getStockByWarehouse($warehouse_id);

                return [
                    'id' => $product->id,
                    'code' => $product->code,
                    'name' => $product->name,
                    'base_price' => $product->base_price,
                    'stock' => $productStock,
                ];
            });

        return response()->json($products);
    }
}
