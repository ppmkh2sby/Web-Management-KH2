<?php

namespace App\Http\Controllers;

use App\Models\WaliSantriRelasi;    
use Illuminate\Support\Facades\Auth;

class WaliDashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        if ($user->id_role != 5) {
            return redirect()->route('dashboard')->with('error', 'Akses hanya untuk Wali Santri');
        }
        $anak = $user->anakSantri; 

        return view('dashboard.wali', compact('anak'));
    }
}
