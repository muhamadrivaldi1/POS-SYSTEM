<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\PriceTier;
use App\Models\ProductBarcode;
use App\Models\ProductPrice;
use App\Models\Warehouse;
use App\Models\WarehouseStock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with(['category', 'barcodes']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            });
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $products = $query->latest()->paginate(20);
        $categories = Category::where('status', 'active')->get();

        return view('products.index', compact('products', 'categories'));
    }

    public function create()
    {
        $categories = Category::where('status', 'active')->get();
        $priceTiers = PriceTier::where('status', 'active')->orderBy('priority')->get();
        $warehouses = Warehouse::where('status', 'active')->get();

        return view('products.create', compact('categories', 'priceTiers', 'warehouses'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required|string|max:255|unique:products',
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'description' => 'nullable|string',
            'purchase_price' => 'required|numeric|min:0',
            'base_price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'min_stock' => 'required|integer|min:0',
            'unit' => 'required|string|max:50',
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'status' => 'required|in:active,inactive',
            'barcodes' => 'nullable|array',
            'barcodes.*.barcode' => 'required|string|unique:product_barcodes,barcode',
            'barcodes.*.packaging_type' => 'nullable|string',
            'barcodes.*.qty_per_package' => 'required|integer|min:1',
            'prices' => 'required|array',
            'prices.*' => 'required|numeric|min:0',
            'warehouse_stocks' => 'nullable|array'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        DB::beginTransaction();
        try {
            // Ambil semua data kecuali barcodes, prices, image, warehouse_stocks
            $data = $request->except(['barcodes', 'prices', 'image', 'warehouse_stocks']);

            // Upload gambar jika ada
            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $imageName = time() . '_' . $image->getClientOriginalName();
                $image->move(public_path('images/products'), $imageName);
                $data['image'] = $imageName;
            }

            // Simpan produk
            $product = Product::create($data);

            // ========================
            // Auto-generate barcode jika tidak ada input
            // ========================
            if (!$request->has('barcodes') || empty($request->barcodes)) {
                ProductBarcode::create([
                    'product_id' => $product->id,
                    'barcode' => 'PRD' . str_pad($product->id, 6, '0', STR_PAD_LEFT),
                    'packaging_type' => 'pcs',
                    'qty_per_package' => 1,
                ]);
            } else {
                foreach ($request->barcodes as $barcodeData) {
                    ProductBarcode::create([
                        'product_id' => $product->id,
                        'barcode' => $barcodeData['barcode'],
                        'packaging_type' => $barcodeData['packaging_type'] ?? 'pcs',
                        'qty_per_package' => $barcodeData['qty_per_package'] ?? 1,
                    ]);
                }
            }

            // Simpan harga bertingkat
            if ($request->has('prices')) {
                foreach ($request->prices as $tierId => $price) {
                    ProductPrice::create([
                        'product_id' => $product->id,
                        'price_tier_id' => $tierId,
                        'price' => $price,
                    ]);
                }
            }

            // Simpan stok per gudang
            if ($request->has('warehouse_stocks')) {
                foreach ($request->warehouse_stocks as $warehouseId => $stock) {
                    if ($stock > 0) {
                        WarehouseStock::create([
                            'warehouse_id' => $warehouseId,
                            'product_id' => $product->id,
                            'stock' => $stock,
                        ]);
                    }
                }
            }

            DB::commit();

            return redirect()->route('products.index')
                ->with('success', 'Produk berhasil ditambahkan');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage())
                ->withInput();
        }
    }


    public function edit(Product $product)
    {
        $product->load(['barcodes', 'prices', 'warehouseStocks']);
        $categories = Category::where('status', 'active')->get();
        $priceTiers = PriceTier::where('status', 'active')->orderBy('priority')->get();
        $warehouses = Warehouse::where('status', 'active')->get();

        return view('products.edit', compact('product', 'categories', 'priceTiers', 'warehouses'));
    }

    public function update(Request $request, Product $product)
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required|string|max:255|unique:products,code,' . $product->id,
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'description' => 'nullable|string',
            'purchase_price' => 'required|numeric|min:0',
            'base_price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'min_stock' => 'required|integer|min:0',
            'unit' => 'required|string|max:50',
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'status' => 'required|in:active,inactive',
            'prices' => 'required|array',
            'prices.*' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        DB::beginTransaction();
        try {
            $data = $request->except(['barcodes', 'prices', 'image', 'warehouse_stocks']);

            if ($request->hasFile('image')) {
                // Hapus gambar lama
                if ($product->image && file_exists(public_path('images/products/' . $product->image))) {
                    unlink(public_path('images/products/' . $product->image));
                }

                $image = $request->file('image');
                $imageName = time() . '_' . $image->getClientOriginalName();
                $image->move(public_path('images/products'), $imageName);
                $data['image'] = $imageName;
            }

            $product->update($data);

            // Update harga bertingkat
            if ($request->has('prices')) {
                foreach ($request->prices as $tierId => $price) {
                    ProductPrice::updateOrCreate(
                        [
                            'product_id' => $product->id,
                            'price_tier_id' => $tierId,
                        ],
                        ['price' => $price]
                    );
                }
            }

            DB::commit();

            return redirect()->route('products.index')
                ->with('success', 'Produk berhasil diupdate');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function destroy(Product $product)
    {
        DB::beginTransaction();
        try {
            if ($product->image && file_exists(public_path('images/products/' . $product->image))) {
                unlink(public_path('images/products/' . $product->image));
            }

            $product->delete();

            DB::commit();

            return redirect()->route('products.index')
                ->with('success', 'Produk berhasil dihapus');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    // Barcode Search / Manage
    public function searchByBarcode(Request $request)
    {
        $barcode = $request->barcode;
        $warehouseId = $request->warehouse_id;  // tambah ini
        $priceTierId = $request->price_tier_id; // tambah ini

        $productBarcode = ProductBarcode::with(['product.category', 'product.prices'])
            ->where('barcode', $barcode)
            ->first();

        if (!$productBarcode) {
            return response()->json([
                'success' => false,
                'message' => 'Barcode tidak ditemukan'
            ], 404);
        }

        $product = $productBarcode->product;

        if ($product->status !== 'active') {
            return response()->json([
                'success' => false,
                'message' => 'Produk tidak aktif'
            ], 400);
        }

        // ambil stok sesuai gudang
        $warehouseStock = WarehouseStock::where('product_id', $product->id)
            ->where('warehouse_id', $warehouseId)
            ->first();
        $stock = $warehouseStock ? $warehouseStock->stock : 0;

        // ambil harga sesuai price tier
        $price = $product->prices()->where('price_tier_id', $priceTierId)->first();
        $priceValue = $price ? $price->price : $product->base_price;

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $product->id,
                'code' => $product->code,
                'name' => $product->name,
                'category' => $product->category->name,
                'price' => $priceValue,
                'stock' => $stock,
                'unit' => $product->unit,
                'barcode' => $barcode,
                'qty_per_package' => $productBarcode->qty_per_package,
                'packaging_type' => $productBarcode->packaging_type,
            ]
        ]);
    }


    public function addBarcode(Request $request, Product $product)
    {
        $validator = Validator::make($request->all(), [
            'barcode' => 'required|string|unique:product_barcodes,barcode',
            'packaging_type' => 'nullable|string',
            'qty_per_package' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $barcode = ProductBarcode::create([
            'product_id' => $product->id,
            'barcode' => $request->barcode,
            'packaging_type' => $request->packaging_type,
            'qty_per_package' => $request->qty_per_package,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Barcode berhasil ditambahkan',
            'data' => $barcode
        ]);
    }

    public function deleteBarcode(ProductBarcode $barcode)
    {
        $barcode->delete();

        return response()->json([
            'success' => true,
            'message' => 'Barcode berhasil dihapus'
        ]);
    }

    public function searchByName(Request $request)
    {
        $keyword = $request->keyword;

        $products = Product::where('name', 'LIKE', "%{$keyword}%")
            ->limit(10)
            ->get();

        return response()->json($products);
    }
}
