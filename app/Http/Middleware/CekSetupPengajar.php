<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CekSetupPengajar
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check() && Auth::user()->role === 'pengajar') {
            $user = Auth::user();
            
            // PERIKSA: Jika data pengajar belum ada ATAU rekeningnya masih kosong
            $isBelumSetup = !$user->pengajar || empty($user->pengajar->nomor_rekening) || empty($user->pengajar->nama_bank);

            if ($isBelumSetup) {
                // Cegah loop jika sedang di halaman setup atau logout
                if (!$request->routeIs('pengajar.setup', 'pengajar.setup.store', 'logout')) {
                    return redirect()->route('pengajar.setup')
                        ->with('warning', 'Silakan lengkapi profil dan data rekening Anda terlebih dahulu.');
                }
            }
        }

        return $next($request);
    }
}