<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Warehouse;
use App\Models\PriceTier;
use App\Models\CashSession;
use Illuminate\Support\Facades\Auth;

class PosController extends Controller
{
    public function index()
    {
        // Ambil semua gudang yang statusnya aktif
        $warehouses = Warehouse::where('status', 'active')
            ->orderByRaw('FIELD(name, "Gudang Cabang", "Gudang Utama")')
            ->get();

        // Ambil semua tipe harga
        $priceTiers = PriceTier::all();

        // Cek apakah user (kasir) sudah membuka sesi kas
        $openSession = CashSession::where('user_id', Auth::id())
            ->where('status', 'open')
            ->latest()
            ->first();

        // Jika user adalah kasir dan belum membuka sesi kas, arahkan ke halaman buka sesi kas
        if (Auth::user()->role === 'kasir' && !$openSession) {
            return redirect()->route('cash-sessions.create')
                ->with('warning', 'Silakan buka sesi kas terlebih dahulu sebelum menggunakan POS');
        }

        // Kirim data ke view POS
        return view('pos.index', compact('warehouses', 'priceTiers', 'openSession'));
    }
}
