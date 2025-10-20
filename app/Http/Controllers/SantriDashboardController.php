<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;

class SantriDashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        if ($user->id_role != 4) {
            return redirect()->route('login')->with('error', 'Akses hanya untuk Santri.');
        }

        return view('dashboard.santri', compact('user'));
    }
}
