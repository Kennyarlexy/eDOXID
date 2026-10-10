<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class IsActive
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if(Auth::user()->status == 'inactive') {
            Auth::logout();
            return redirect()->route('login')->with('failed', 'Akun anda di non aktifkan, harap hubungi pihak berwajib untuk info lebih lanjut.');;
        }
        return $next($request);
    }
}
