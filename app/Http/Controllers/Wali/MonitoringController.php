<?php

namespace App\Http\Controllers\Wali;

use App\Http\Controllers\Controller;
use App\Models\Kehadiran;
use App\Models\Santri;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class MonitoringController extends Controller
{
    /**
     * Ambil daftar santri yang terhubung dengan wali saat ini.
     */
    protected function connectedChildren(): Collection
    {
        /** @var BelongsToMany $relation */
        $relation = Auth::user()
            ->waliOf()
            ->with(['user', 'kelas']);

        return $relation->get();
    }

    /**
     * Pastikan kode santri memang milik wali terkait.
     */
    protected function loadSantri(string $santriCode): Santri
    {
        /** @var BelongsToMany $relation */
        $relation = Auth::user()
            ->waliOf()
            ->with(['user', 'kelas']);

        $santri = $relation
            ->where('santris.code', $santriCode)
            ->first();

        abort_if(! $santri, 404);

        return $santri;
    }

    /**
     * Return query builder untuk model bila tersedia.
     */
    private function q(string $class): ?Builder
    {
        return class_exists($class) ? $class::query() : null;
    }

    public function overview(string $santriCode): View
    {
        $santri = $this->loadSantri($santriCode);
        $today = Carbon::today();

        $K = $this->q(Kehadiran::class);
        $hadir = $K ? (clone $K)->where('santri_id', $santri->id)->whereMonth('tanggal', now()->month)->where('status', 'hadir')->count() : 0;
        $izin = $K ? (clone $K)->where('santri_id', $santri->id)->whereMonth('tanggal', now()->month)->where('status', 'izin')->count() : 0;
        $alpa = $K ? (clone $K)->where('santri_id', $santri->id)->whereMonth('tanggal', now()->month)->where('status', 'alpa')->count() : 0;

        $J = $this->q('App\\Models\\JadwalPelajaran');
        $jadwalHariIni = $J
            ? (clone $J)->with(['mapel', 'guru'])->where('kelas_id', optional($santri->kelas)->id)->whereDate('tanggal', $today)->orderBy('jam_mulai')->get()
            : collect();

        $P = $this->q('App\\Models\\Pengumuman');
        $pengumuman = $P ? (clone $P)->latest()->take(4)->get() : collect();

        $emailVerified = ! is_null(Auth::user()->email_verified_at);
        $santriList = $this->connectedChildren();

        return view('wali.pages.overview', compact(
            'santri',
            'santriList',
            'hadir',
            'izin',
            'alpa',
            'jadwalHariIni',
            'pengumuman',
            'emailVerified'
        ));
    }

    public function presensi(string $santriCode): View
    {
        $santri = $this->loadSantri($santriCode);
        $santriList = $this->connectedChildren();
        $data = $this->fetchKehadiran($santri);

        return view('wali.pages.data.presensi', compact('santri', 'santriList', 'data'));
    }

    public function progres(string $santriCode): View
    {
        $santri = $this->loadSantri($santriCode);
        $santriList = $this->connectedChildren();
        $items = $this->fetchProgress($santri);

        return view('wali.pages.data.progres', compact('santri', 'santriList', 'items'));
    }

    public function log(string $santriCode): View
    {
        $santri = $this->loadSantri($santriCode);
        $santriList = $this->connectedChildren();
        $logs = $this->fetchLogKeluarMasuk($santri);

        return view('wali.pages.data.log', compact('santri', 'santriList', 'logs'));
    }

    private function fetchKehadiran(Santri $santri): Collection
    {
        $K = $this->q(Kehadiran::class);

        if ($K) {
            return (clone $K)
                ->where('santri_id', $santri->id)
                ->latest('tanggal')
                ->take(60)
                ->get();
        }

        return collect([
            ['tanggal' => Carbon::today()->toDateString(), 'status' => 'hadir', 'keterangan' => 'Pengajian Kitab Ba\'da Subuh'],
            ['tanggal' => Carbon::yesterday()->toDateString(), 'status' => 'hadir', 'keterangan' => 'Tahfidz Pagi'],
            ['tanggal' => Carbon::today()->subDays(2)->toDateString(), 'status' => 'izin', 'keterangan' => 'Kontrol kesehatan'],
            ['tanggal' => Carbon::today()->subDays(3)->toDateString(), 'status' => 'telat', 'keterangan' => 'Kajian Hadits'],
            ['tanggal' => Carbon::today()->subDays(4)->toDateString(), 'status' => 'alpa', 'keterangan' => 'Belum mengisi presensi'],
        ])->map(fn ($row) => (object) $row);
    }

    private function fetchProgress(Santri $santri): Collection
    {
        $ProgressModel = $this->q(\App\Models\ProgressKeilmuan::class);
        $items = $ProgressModel
            ? (clone $ProgressModel)->where('santri_id', $santri->id)->latest()->get()
            : collect();

        if ($items->isEmpty()) {
            $items = collect([
                [
                    'judul' => 'Hafalan Juz 30',
                    'target' => 20,
                    'capaian' => 15,
                    'satuan' => 'surah',
                    'level' => 'Intermediate',
                    'catatan' => 'Fokuskan murajaah pada surah pendek menjelang tasmi\'.',
                    'pembimbing' => 'Ust. Abdullah',
                    'terakhir' => Carbon::today()->subDays(1),
                ],
                [
                    'judul' => 'Fiqih Ibadah',
                    'target' => 8,
                    'capaian' => 5,
                    'satuan' => 'bab',
                    'level' => 'Fundamental',
                    'catatan' => 'Bab thaharah perlu diulang sebelum ujian lisan.',
                    'pembimbing' => 'Ust. Farhan',
                    'terakhir' => Carbon::today()->subDays(2),
                ],
                [
                    'judul' => 'Bahasa Arab',
                    'target' => 12,
                    'capaian' => 9,
                    'satuan' => 'modul',
                    'level' => 'Advance',
                    'catatan' => 'Pertahankan setoran harian mufrodat baru.',
                    'pembimbing' => 'Ustadzah Alia',
                    'terakhir' => Carbon::today()->subDays(3),
                ],
            ])->map(function ($item) {
                $item['persentase'] = $item['target'] ? round(($item['capaian'] / $item['target']) * 100) : 0;
                return (object) $item;
            });
        } else {
            $items = $items->map(function ($item) {
                $item->persentase = $item->target ? round(($item->capaian / $item->target) * 100) : 0;
                return $item;
            });
        }

        return $items;
    }

    private function fetchLogKeluarMasuk(Santri $santri): Collection
    {
        $LogModel = $this->q(\App\Models\LogKeluarMasuk::class);
        $logs = $LogModel
            ? (clone $LogModel)->where('santri_id', $santri->id)->latest('tanggal_pengajuan')->get()
            : collect();

        if ($logs->isEmpty()) {
            $logs = collect([
                [
                    'tanggal_pengajuan' => Carbon::today()->subDays(1)->toDateString(),
                    'jenis' => 'Keluar Pondok',
                    'rentang' => '13.00 - 16.00',
                    'status' => 'disetujui',
                    'catatan' => 'Kontrol kesehatan di Puskesmas',
                    'petugas' => 'Ust. Fathur',
                ],
                [
                    'tanggal_pengajuan' => Carbon::today()->subDays(5)->toDateString(),
                    'jenis' => 'Cuti Akhir Pekan',
                    'rentang' => 'Sabtu - Ahad',
                    'status' => 'proses',
                    'catatan' => 'Undangan keluarga',
                    'petugas' => 'Ust. Dani',
                ],
                [
                    'tanggal_pengajuan' => Carbon::today()->subDays(8)->toDateString(),
                    'jenis' => 'Kembali ke Pondok',
                    'rentang' => '21.15',
                    'status' => 'tercatat',
                    'catatan' => 'Setelah bakti sosial',
                    'petugas' => 'Satpam Utama',
                ],
            ])->map(fn ($row) => (object) $row);
        }

        return $logs;
    }
}
