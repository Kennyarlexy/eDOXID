<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class Dosen
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
        if(Auth::user()->role != User::ROLE_DOSEN){
            return redirect()->route('panel.dashboard')->with('failed', 'Anda tidak memiliki hak akses pada halaman tersebut.');;
        }
        return $next($request);
    }
}
