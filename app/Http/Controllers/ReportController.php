<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportController extends Controller
{
    public function sales(Request $request)
    {
        $query = Transaction::with(['user', 'details'])
            ->where('status', 'completed');

        if ($request->filled('date_from')) {
            $query->whereDate('transaction_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('transaction_date', '<=', $request->date_to);
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        $transactions = $query->latest('transaction_date')->get();

        $totalSales = $transactions->sum('total');
        $totalTransactions = $transactions->count();
        $totalDiscount = $transactions->sum('discount_amount');
        $totalTax = $transactions->sum('tax_amount');

        $users = User::all();

        if ($request->has('export') && $request->export === 'pdf') {
            $pdf = Pdf::loadView('reports.sales_pdf', compact(
                'transactions', 'totalSales', 'totalTransactions', 
                'totalDiscount', 'totalTax', 'request'
            ));
            return $pdf->download('laporan-penjualan.pdf');
        }

        return view('reports.sales', compact(
            'transactions', 'totalSales', 'totalTransactions', 
            'totalDiscount', 'totalTax', 'users'
        ));
    }

    public function stock()
    {
        $products = Product::with(['category', 'warehouseStocks.warehouse'])
            ->where('status', 'active')
            ->get();

        $lowStock = $products->filter(function($product) {
            return $product->stock <= $product->min_stock;
        });

        return view('reports.stock', compact('products', 'lowStock'));
    }

    public function bestSelling(Request $request)
    {
        $dateFrom = $request->date_from ?? now()->startOfMonth()->toDateString();
        $dateTo = $request->date_to ?? now()->toDateString();

        $products = DB::table('transaction_details')
            ->join('transactions', 'transaction_details.transaction_id', '=', 'transactions.id')
            ->join('products', 'transaction_details.product_id', '=', 'products.id')
            ->whereBetween('transactions.transaction_date', [$dateFrom, $dateTo])
            ->where('transactions.status', 'completed')
            ->select(
                'products.name',
                'products.code',
                DB::raw('SUM(transaction_details.qty) as total_qty'),
                DB::raw('SUM(transaction_details.subtotal) as total_revenue')
            )
            ->groupBy('products.id', 'products.name', 'products.code')
            ->orderByDesc('total_qty')
            ->limit(50)
            ->get();

        return view('reports.best_selling', compact('products', 'dateFrom', 'dateTo'));
    }
}
