<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Warehouse;
use App\Models\PriceTier;
use App\Models\CashSession;
use App\Models\Product;

class PosController extends Controller
{
    /**
     * Halaman POS
     */
    public function index()
    {
        $warehouses = Warehouse::where('status', 'active')
            ->orderBy('name')
            ->get();

        $priceTiers = PriceTier::where('status', 'active')->get();

        $openSession = CashSession::where('user_id', Auth::id())
            ->where('status', 'open')
            ->latest()
            ->first();

        if (Auth::user()->role === 'kasir' && !$openSession) {
            return redirect()
                ->route('cash-sessions.create')
                ->with('warning', 'Silakan buka sesi kas terlebih dahulu');
        }

        return view('pos.index', compact(
            'warehouses',
            'priceTiers',
            'openSession'
        ));
    }

    /**
     * 🔍 Scan Barcode
     */
    public function searchByBarcode(Request $request)
    {
        $barcode = $request->barcode;

        $gudangUtamaId = Warehouse::where('name', 'Gudang Utama')->value('id');
        $tokoId = Warehouse::where('name', 'Toko')->value('id');

        $product = DB::table('products as p')
            ->leftJoin('warehouse_stocks as ws_gudang', function ($join) use ($gudangUtamaId) {
                $join->on('p.id', '=', 'ws_gudang.product_id')
                    ->where('ws_gudang.warehouse_id', $gudangUtamaId);
            })
            ->leftJoin('warehouse_stocks as ws_toko', function ($join) use ($tokoId) {
                $join->on('p.id', '=', 'ws_toko.product_id')
                    ->where('ws_toko.warehouse_id', $tokoId);
            })
            ->where('p.code', $barcode)
            ->select(
                'p.id',
                'p.code',
                'p.name',
                'p.base_price',
                DB::raw('COALESCE(ws_gudang.stock, 0) as stock_gudang'),
                DB::raw('COALESCE(ws_toko.stock, 0) as stock_toko')
            )
            ->first();

        if (!$product) {
            return response()->json(['success' => false]);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $product->id,
                'code' => $product->code,
                'name' => $product->name,
                'base_price' => $product->base_price,
                'gudang' => (int) $product->stock_gudang,
                'toko' => (int) $product->stock_toko,
                'stock' => (int) $product->stock_toko // stok utama POS
            ]
        ]);
    }

    /**
     * 🔍 Cari berdasarkan nama / kode (MANUAL SEARCH)
     */
        public function searchByName(Request $request)
{
    $keyword = $request->keyword;
    $warehouseId = $request->warehouse_id;

    $products = DB::table('products as p')
        ->leftJoin('warehouse_stocks as ws', function ($join) use ($warehouseId) {
            $join->on('p.id', '=', 'ws.product_id');

            if ($warehouseId) {
                $join->where('ws.warehouse_id', $warehouseId);
            }
        })
        ->where(function ($q) use ($keyword) {
            $q->where('p.name', 'like', "%{$keyword}%")
              ->orWhere('p.code', 'like', "%{$keyword}%");
        })
        ->select(
            'p.id',
            'p.code',
            'p.name',
            'p.base_price',
            DB::raw('COALESCE(ws.stock, 0) as stock')
        )
        ->orderBy('p.name')
        ->limit(20)
        ->get();

    return response()->json([
        'success' => true,
        'data' => $products
    ]);
}


}
