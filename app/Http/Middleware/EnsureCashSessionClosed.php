<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\CashSession;

class EnsureCashSessionClosed
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
     public function handle(Request $request, Closure $next)
    {
        if (Auth::check() && Auth::user()->role === 'kasir') {
            $openSession = CashSession::where('user_id', Auth::id())
                ->where('status', 'open')
                ->latest()
                ->first();

            if ($openSession) {
                return redirect()->route('cash-sessions.show', $openSession)
                    ->with('warning', 'Anda harus menutup sesi kas sebelum logout');
            }
        }

        
        return $next($request);
    }
}
