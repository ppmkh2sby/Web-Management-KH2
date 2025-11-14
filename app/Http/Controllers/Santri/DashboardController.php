<?php

namespace App\Http\Controllers\Santri;

use App\Http\Controllers\Controller;
use App\Models\Santri;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class DashboardController extends Controller
{
    protected function loadSantri()
    {
        $user = Auth::user();
        return Santri::with(['kelas','walis','user'])->where('user_id', $user->id)->firstOrFail();
    }

    /** Return query builder untuk model bila ada, selain itu null */
    private function q(string $class)
    {
        return class_exists($class) ? $class::query() : null;
    }

    public function home(): View
    {
        $santri = $this->loadSantri();
        $today  = Carbon::today();

        // ---- Kehadiran (aman jika model tidak ada)
        $K = $this->q(\App\Models\Kehadiran::class);
        $hadir = $K ? (clone $K)->where('santri_id', $santri->id)->whereMonth('tanggal', now()->month)->where('status','hadir')->count() : 0;
        $izin  = $K ? (clone $K)->where('santri_id', $santri->id)->whereMonth('tanggal', now()->month)->where('status','izin')->count()  : 0;
        $alpa  = $K ? (clone $K)->where('santri_id', $santri->id)->whereMonth('tanggal', now()->month)->where('status','alpa')->count()  : 0;

        // ---- Jadwal hari ini
        $J = $this->q(\App\Models\JadwalPelajaran::class);
        $jadwalHariIni = $J
            ? (clone $J)->with('mapel','guru')->where('kelas_id', optional($santri->kelas)->id)->whereDate('tanggal', $today)->orderBy('jam_mulai')->get()
            : collect(); // fallback: koleksi kosong

        // ---- Pengumuman terbaru
        $P = $this->q(\App\Models\Pengumuman::class);
        $pengumuman = $P ? (clone $P)->latest()->take(4)->get() : collect();

        $emailVerified = !is_null(Auth::user()->email_verified_at);

        return view('santri.pages.home', compact(
            'santri','hadir','izin','alpa','jadwalHariIni','pengumuman','emailVerified'
        ));
    }

    public function profile(): View
    {
        $santri = $this->loadSantri();
        return view('santri.pages.profile', compact('santri'));
    }

    public function setting(): View
    {
        return view('santri.pages.setting');
    }

    public function dataIndex(): View
    {
        $santri = $this->loadSantri();
        return view('santri.pages.data.index', compact('santri'));
    }

    public function presensi(): View
    {
        $santri = $this->loadSantri();
        $K = $this->q(\App\Models\Kehadiran::class);
        $data = $K ? (clone $K)->where('santri_id',$santri->id)->latest('tanggal')->take(30)->get() : collect();
        return view('santri.pages.data.presensi', compact('santri','data'));
    }

    public function progres(): View
    {
        $santri = $this->loadSantri();
        $items = collect(); 
        return view('santri.pages.data.progres', compact('santri','items'));
    }

    public function log(): View
    {
        $santri = $this->loadSantri();
        $logs = collect(); 
        return view('santri.pages.data.log', compact('santri','logs'));
    }
}
