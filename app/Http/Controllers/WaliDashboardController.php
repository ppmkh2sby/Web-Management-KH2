<?php

namespace App\Http\Controllers;

use App\Models\WaliSantriRelasi;
use Illuminate\Support\Facades\Auth;

class WaliDashboardController extends Controller
{
    public function index()
    {
        $waliId = Auth::id();
        $anak = WaliSantriRelasi::with('santri')
                ->where('id_wali', $waliId)
                ->get()
                ->pluck('santri');

        return view('dashboard.wali', compact('anak'));
    }
}
