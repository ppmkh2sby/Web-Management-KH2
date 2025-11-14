<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;

class SantriDashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        // Middleware 'role:santri' sudah melindungi
        return view('dashboard.santri', compact('user'));
    }
}
