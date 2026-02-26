<?php

namespace App\Http\Controllers\Wali;

use App\Http\Controllers\Controller;
use App\Models\Kehadiran;
use App\Models\Santri;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class MonitoringController extends Controller
{
    private ?Collection $connectedChildrenCache = null;

    public function main(): RedirectResponse
    {
        $firstChildCode = $this->connectedChildren()
            ->sortBy('nama_lengkap')
            ->first()
            ?->code;

        if (! filled($firstChildCode)) {
            return redirect()
                ->route('profile.edit')
                ->with('status', 'Akun wali belum terhubung ke data anak.');
        }

        return redirect()->route('wali.anak.overview', ['santriCode' => $firstChildCode]);
    }

    /**
     * Ambil daftar santri yang terhubung dengan wali saat ini.
     */
    protected function connectedChildren(): Collection
    {
        if ($this->connectedChildrenCache instanceof Collection) {
            return $this->connectedChildrenCache;
        }

        /** @var BelongsToMany $relation */
        $relation = Auth::user()
            ->waliOf()
            ->with(['user', 'kelas']);

        $this->connectedChildrenCache = $relation->get();

        return $this->connectedChildrenCache;
    }

    /**
     * Pastikan kode santri memang milik wali terkait.
     */
    protected function loadSantri(string $santriCode): Santri
    {
        $santri = $this->connectedChildren()->firstWhere('code', $santriCode);

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
        $santriList = $this->connectedChildren();
        $emailVerified = ! is_null(Auth::user()->email_verified_at);

        $kehadiranRows = $this->fetchKehadiran($santri);
        $hadir = $kehadiranRows->where('status', 'hadir')->count();
        $izin = $kehadiranRows->where('status', 'izin')->count();
        $alpa = $kehadiranRows->where('status', 'alpa')->count();
        $kehadiranTotal = $kehadiranRows->count();
        $kehadiranPercent = $kehadiranTotal > 0 ? (int) round(($hadir / $kehadiranTotal) * 100) : 0;
        $kehadiranRecent = $kehadiranRows->take(5);

        $progressItems = $this->fetchProgress($santri);
        $progressTotal = $progressItems->count();
        $progressCompleted = $progressItems->filter(fn ($item) => (int) ($item->persentase ?? 0) >= 100)->count();
        $progressInProgress = max($progressTotal - $progressCompleted, 0);
        $progressAverage = $progressTotal > 0 ? (int) round($progressItems->avg(fn ($item) => (int) ($item->persentase ?? 0))) : 0;
        $progressRecent = $progressItems
            ->sortByDesc(fn ($item) => $item->terakhir_setor ?? $item->updated_at ?? null)
            ->take(5)
            ->values();

        $logRows = $this->fetchLogKeluarMasuk($santri);
        $logTotal = $logRows->count();
        $logThisMonth = $logRows->filter(function ($row) {
            $date = $row->tanggal_pengajuan ?? null;
            return $date && Carbon::parse($date)->isSameMonth(now());
        })->count();
        $logRecent = $logRows->take(5);

        return view('wali.pages.overview', compact(
            'santri',
            'santriList',
            'emailVerified',
            'hadir',
            'izin',
            'alpa',
            'kehadiranTotal',
            'kehadiranPercent',
            'kehadiranRecent',
            'progressTotal',
            'progressCompleted',
            'progressInProgress',
            'progressAverage',
            'progressRecent',
            'logTotal',
            'logThisMonth',
            'logRecent'
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
                    'terakhir_setor' => Carbon::today()->subDays(1),
                ],
                [
                    'judul' => 'Fiqih Ibadah',
                    'target' => 8,
                    'capaian' => 5,
                    'satuan' => 'bab',
                    'level' => 'Fundamental',
                    'catatan' => 'Bab thaharah perlu diulang sebelum ujian lisan.',
                    'pembimbing' => 'Ust. Farhan',
                    'terakhir_setor' => Carbon::today()->subDays(2),
                ],
                [
                    'judul' => 'Bahasa Arab',
                    'target' => 12,
                    'capaian' => 9,
                    'satuan' => 'modul',
                    'level' => 'Advance',
                    'catatan' => 'Pertahankan setoran harian mufrodat baru.',
                    'pembimbing' => 'Ustadzah Alia',
                    'terakhir_setor' => Carbon::today()->subDays(3),
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
        return $LogModel
            ? (clone $LogModel)->where('santri_id', $santri->id)->latest('tanggal_pengajuan')->get()
            : collect();
    }
}
