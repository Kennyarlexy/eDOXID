<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class Mahasiswa
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
        if(Auth::user()->role != User::ROLE_MAHASISWA){
            return redirect()->route('panel.dashboard')->with('failed', 'Anda tidak memiliki hak akses pada halaman tersebut.');;
        }
        return $next($request);
    }
}
