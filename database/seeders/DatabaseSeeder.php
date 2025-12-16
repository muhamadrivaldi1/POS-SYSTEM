<?php
// database/seeders/DatabaseSeeder.php
namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        $this->call([
            UserSeeder::class,
            CategorySeeder::class,
            PriceTierSeeder::class,
            WarehouseSeeder::class,
        ]);
    }
}

// database/seeders/UserSeeder.php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run()
    {
        User::create([
            'username' => 'admin',
            'email' => 'admin@pos.com',
            'password' => Hash::make('admin123'),
            'full_name' => 'Administrator',
            'role' => 'admin',
            'status' => 'active',
        ]);

        User::create([
            'username' => 'supervisor',
            'email' => 'supervisor@pos.com',
            'password' => Hash::make('supervisor123'),
            'full_name' => 'Supervisor',
            'role' => 'supervisor',
            'status' => 'active',
        ]);

        User::create([
            'username' => 'kasir1',
            'email' => 'kasir1@pos.com',
            'password' => Hash::make('kasir123'),
            'full_name' => 'Kasir 1',
            'role' => 'kasir',
            'status' => 'active',
        ]);
    }
}

// database/seeders/CategorySeeder.php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    public function run()
    {
        $categories = [
            ['name' => 'Makanan', 'description' => 'Produk makanan', 'status' => 'active'],
            ['name' => 'Minuman', 'description' => 'Produk minuman', 'status' => 'active'],
            ['name' => 'Elektronik', 'description' => 'Produk elektronik', 'status' => 'active'],
            ['name' => 'Fashion', 'description' => 'Produk fashion', 'status' => 'active'],
            ['name' => 'Alat Tulis', 'description' => 'Produk alat tulis', 'status' => 'active'],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }
    }
}

// database/seeders/PriceTierSeeder.php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PriceTier;

class PriceTierSeeder extends Seeder
{
    public function run()
    {
        $tiers = [
            [
                'name' => 'Retail',
                'description' => 'Harga retail untuk konsumen umum',
                'priority' => 1,
                'status' => 'active',
            ],
            [
                'name' => 'Grosir',
                'description' => 'Harga grosir untuk pembelian dalam jumlah besar',
                'priority' => 2,
                'status' => 'active',
            ],
            [
                'name' => 'Member',
                'description' => 'Harga khusus untuk member',
                'priority' => 3,
                'status' => 'active',
            ],
        ];

        foreach ($tiers as $tier) {
            PriceTier::create($tier);
        }
    }
}

// database/seeders/WarehouseSeeder.php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Warehouse;

class WarehouseSeeder extends Seeder
{
    public function run()
    {
        $warehouses = [
            [
                'name' => 'Gudang Utama',
                'address' => 'Jl. Raya Industri No. 123',
                'phone' => '021-1234567',
                'status' => 'active',
            ],
            [
                'name' => 'Gudang Cabang',
                'address' => 'Jl. Raya Cabang No. 456',
                'phone' => '021-7654321',
                'status' => 'active',
            ],
        ];

        foreach ($warehouses as $warehouse) {
            Warehouse::create($warehouse);
        }
    }
}

// database/seeders/ProductSeeder.php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\ProductPrice;
use App\Models\WarehouseStock;

class ProductSeeder extends Seeder
{
    public function run()
    {
        $products = [
            [
                'code' => 'M001',
                'name' => 'Nasi Goreng',
                'category_id' => 1, // Makanan
                'description' => 'Nasi goreng spesial',
                'purchase_price' => 10000,
                'base_price' => 15000,
                'stock' => 50,
                'min_stock' => 5,
                'unit' => 'porsi',
                'status' => 'active',
            ],
            [
                'code' => 'D001',
                'name' => 'Teh Botol',
                'category_id' => 2, // Minuman
                'description' => 'Teh botol manis',
                'purchase_price' => 3000,
                'base_price' => 5000,
                'stock' => 100,
                'min_stock' => 10,
                'unit' => 'botol',
                'status' => 'active',
            ],
            [
                'code' => 'E001',
                'name' => 'Headphone',
                'category_id' => 3, // Elektronik
                'description' => 'Headphone nirkabel',
                'purchase_price' => 150000,
                'base_price' => 200000,
                'stock' => 20,
                'min_stock' => 2,
                'unit' => 'pcs',
                'status' => 'active',
            ],
        ];

        foreach ($products as $p) {
            $product = Product::create($p);

            // Harga bertingkat
            $priceTiers = [1 => $p['base_price'], 2 => $p['base_price'] * 0.9, 3 => $p['base_price'] * 0.95];
            foreach ($priceTiers as $tierId => $price) {
                ProductPrice::create([
                    'product_id' => $product->id,
                    'price_tier_id' => $tierId,
                    'price' => $price,
                ]);
            }

            // Stok per gudang
            WarehouseStock::create([
                'warehouse_id' => 1,
                'product_id' => $product->id,
                'stock' => $product->stock,
            ]);
        }
    }
}
