<?php
namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\Product;
use App\Models\User;
use App\Models\CashSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        // Pastikan user sudah login
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();
        
        // Data untuk semua role
        $todaySales = Transaction::whereDate('transaction_date', today())
            ->where('status', 'completed')
            ->sum('total');
        
        $todayTransactions = Transaction::whereDate('transaction_date', today())
            ->where('status', 'completed')
            ->count();

        // Data tambahan untuk admin/supervisor
        if ($user->role === 'admin' || $user->role === 'supervisor') {
            $monthSales = Transaction::whereMonth('transaction_date', now()->month)
                ->whereYear('transaction_date', now()->year)
                ->where('status', 'completed')
                ->sum('total');
            
            $lowStockProducts = Product::whereRaw('stock <= min_stock')
                ->where('status', 'active')
                ->count();
            
            $topProducts = DB::table('transaction_details')
                ->join('transactions', 'transaction_details.transaction_id', '=', 'transactions.id')
                ->join('products', 'transaction_details.product_id', '=', 'products.id')
                ->whereDate('transactions.transaction_date', today())
                ->where('transactions.status', 'completed')
                ->select('products.name', DB::raw('SUM(transaction_details.qty) as total_qty'))
                ->groupBy('products.id', 'products.name')
                ->orderByDesc('total_qty')
                ->limit(5)
                ->get();

            return view('dashboard', compact(
                'todaySales', 'todayTransactions', 'monthSales', 
                'lowStockProducts', 'topProducts'
            ));
        }

        // Data untuk kasir
        $openSession = CashSession::where('user_id', $user->id)
            ->where('status', 'open')
            ->latest()
            ->first();

        $mySales = Transaction::where('user_id', $user->id)
            ->whereDate('transaction_date', today())
            ->where('status', 'completed')
            ->count();

        return view('dashboard', compact(
            'todaySales', 'todayTransactions', 'openSession', 'mySales'
        ));
    }
}