<?php

namespace App\Http\Controllers;

use App\Models\CashSession;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CashSessionController extends Controller
{
    public function index()
    {
        $sessions = CashSession::with('user')
            ->latest('opened_at')
            ->paginate(20);

        return view('cash_sessions.index', compact('sessions'));
    }

    public function create()
    {
        $openSession = CashSession::where('user_id', auth()->id())
            ->where('status', 'open')
            ->latest()
            ->first();

        if ($openSession) {
            return redirect()->route('cash-sessions.show', $openSession)
                ->with('warning', 'Anda masih memiliki sesi kas yang terbuka');
        }

        return view('cash_sessions.create');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'opening_balance' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $openSession = CashSession::where('user_id', auth()->id())
            ->where('status', 'open')
            ->latest()
            ->first();

        if ($openSession) {
            return redirect()->back()
                ->with('error', 'Anda masih memiliki sesi kas yang terbuka');
        }

        $session = CashSession::create([
            'user_id' => auth()->id(),
            'opening_cash' => $request->opening_balance,
            'opened_at' => now(),
            'status' => 'open',
        ]);

        return redirect()->route('pos.index')
            ->with('success', 'Sesi kas berhasil dibuka');
    }

    public function show(CashSession $cashSession)
    {
        $transactions = Transaction::where('user_id', $cashSession->user_id)
            ->whereBetween('transaction_date', [$cashSession->opened_at, $cashSession->closed_at ?? now()])
            ->where('status', 'completed')
            ->get();

        $totalCash = $transactions->where('payment_method', 'cash')->sum('total');
        $totalCard = $transactions->where('payment_method', 'card')->sum('total');
        $totalEwallet = $transactions->where('payment_method', 'ewallet')->sum('total');
        $totalTransfer = $transactions->where('payment_method', 'transfer')->sum('total');

        $expectedBalance = $cashSession->opening_balance + $totalCash;

        return view('cash_sessions.show', compact(
            'cashSession', 'transactions', 'totalCash', 'totalCard',
            'totalEwallet', 'totalTransfer', 'expectedBalance'
        ));
    }

    public function close(Request $request, CashSession $cashSession)
    {
        if ($cashSession->status === 'closed') {
            return redirect()->back()
                ->with('error', 'Sesi kas sudah ditutup');
        }

        if ($cashSession->user_id !== auth()->id()) {
            return redirect()->back()
                ->with('error', 'Anda tidak dapat menutup sesi kas orang lain');
        }

        $validator = Validator::make($request->all(), [
            'closing_balance' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $totalCash = Transaction::where('user_id', $cashSession->user_id)
            ->whereBetween('transaction_date', [$cashSession->opened_at, now()])
            ->where('status', 'completed')
            ->where('payment_method', 'cash')
            ->sum('total');

        $expectedBalance = $cashSession->opening_balance + $totalCash;
        $difference = $request->closing_balance - $expectedBalance;

        $cashSession->update([
            'closing_balance' => $request->closing_balance,
            'expected_balance' => $expectedBalance,
            'difference' => $difference,
            'closed_at' => now(),
            'status' => 'closed',
            'notes' => $request->notes,
        ]);

        return redirect()->route('cash-sessions.index')
            ->with('success', 'Sesi kas berhasil ditutup');
    }
}