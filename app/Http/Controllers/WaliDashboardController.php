<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;

class WaliDashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $anak = $user->anakSantri; // ambil anak terhubung via pivot
        return view('dashboard.wali', compact('anak'));
    }
}
