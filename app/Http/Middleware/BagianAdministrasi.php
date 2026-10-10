<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BagianAdministrasi
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
        if(Auth::user()->role != User::ROLE_BAGIAN_ADMINISTRASI){
            return redirect()->route('panel.dashboard')->with('failed', 'Anda tidak memiliki hak akses pada halaman tersebut.');;
        }
        return $next($request);
    }
}
