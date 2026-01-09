<?php

namespace App\Http\Controllers\Santri;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePresensiRequest;
use App\Http\Requests\UpdatePresensiRequest;
use App\Models\Kegiatan;
use App\Models\Presensi;
use App\Models\Santri;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PresensiController extends Controller
{
    private function ensureAllowedRole(): void
    {
        abort_unless(auth()->check(), 403);

        $role = auth()->user()->role;
        $allowed = [\App\Enum\Role::SANTRI];

        abort_unless(in_array($role, $allowed, true) || auth()->user()->isKetertiban(), 403);
    }

    /**
     * Backward-compatible guard used by store/update/destroy.
     * Mirrors ensureAllowedRole so non-santri roles (ketertiban/pengurus/degur) can write presensi.
     */
    private function ensureSantriRole(): void
    {
        $this->ensureAllowedRole();
    }

    public function index(): View
    {
        $this->ensureAllowedRole();
        $this->authorize('viewAny', Presensi::class);

        $user = auth()->user();
        $isKetertiban = $user->isKetertiban();
        $santriId = $user->santri?->id;

        $mode = request()->get('mode', $isKetertiban ? 'input' : 'mine');
        if (!in_array($mode, ['input', 'mine', 'team'], true)) {
            $mode = $isKetertiban ? 'input' : 'mine';
        }

        // Paksa santri non-KTB hanya ke mode "mine"
        if ($user->role === \App\Enum\Role::SANTRI && ! $isKetertiban) {
            $mode = 'mine';
        }

        // Gender filter for ketertiban (putra/putri)
        $gender = request()->get('gender', 'putra');
        if (!in_array($gender, ['putra','putri'], true)) {
            $gender = 'putra';
        }

        $putraNames = [
            'Alwida Rahmat',
            "Fahmi Rosyidin Al'Ulya",
            'Keisha Zafif Fahrezi',
            'Maestro Rafa Agniya',
            'Muhammad Farizky Alfath Muhardian Putra',
            'Muhammad Farrel Al-Aqso',
            'Muhammad Setyo Arfan Ibrahim',
            'Zaky Afifi Arif',
        ];

        $putriNames = [
            'Ayesha Nayyara Putri Wuryadi',
            'Azzahra Jamalullaily Mafaza',
            'Cherfine An-Nisaul Auliya Ulla',
            'Deven Kartika Wijaya',
            'Maritza Dara Athifa',
            'Rara Arimbi Gita Atmodjo',
            'Renata Keysha Azalia',
            'Syahdinda Sherlyta Laura',
            'Zahra Suciana Tri Amma Maretha',
        ];

        $query = Presensi::with(['santri', 'kegiatan'])->latest('updated_at');

        if ($isKetertiban && $mode === 'input') {
            $query->whereHas('santri', function ($q) use ($gender) {
                $q->where('gender', $gender);
            });
        } elseif ($mode === 'mine' && $user->role === \App\Enum\Role::SANTRI) {
            abort_unless($santriId, 403);
            $query->where('santri_id', $santriId);

            // Add filters for mine mode
            $statusFilterMine = request()->get('status_filter_mine', []);
            if (!empty($statusFilterMine)) {
                $query->whereIn('status', (array)$statusFilterMine);
            }

            $kategoriFilterMine = request()->get('kategori_filter_mine', []);
            if (!empty($kategoriFilterMine)) {
                $query->whereHas('kegiatan', fn ($kq) => $kq->whereIn('kategori', (array)$kategoriFilterMine));
            }

            $waktuFilterMine = request()->get('waktu_filter_mine', []);
            if (!empty($waktuFilterMine)) {
                $query->whereHas('kegiatan', fn ($kq) => $kq->whereIn('waktu', (array)$waktuFilterMine));
            }

            $tanggalFilterMine = request()->get('tanggal_mine');
            if ($tanggalFilterMine) {
                $query->whereDate('created_at', $tanggalFilterMine);
            }

            $bulanFilterMine = request()->get('bulan_mine');
            if ($bulanFilterMine) {
                $query->whereYear('created_at', substr($bulanFilterMine, 0, 4))
                      ->whereMonth('created_at', substr($bulanFilterMine, 5, 2));
            }

            // Search filter for mine mode
            $searchMine = trim((string) request()->get('search_mine', ''));
            if ($searchMine !== '') {
                $query->where(function ($q) use ($searchMine) {
                    $q->where('nama', 'like', "%{$searchMine}%")
                        ->orWhere('catatan', 'like', "%{$searchMine}%")
                        ->orWhereHas('kegiatan', fn ($kq) => $kq->where('kategori', 'like', "%{$searchMine}%"));
                });
            }
        } elseif ($mode === 'team') {
            // Show all santri - accessible by ketertiban, pengurus, degur
        } else {
            abort(403);
        }

        $search = trim((string) request()->get('search', ''));
        if ($search !== '') {
            $driver = DB::connection()->getDriverName();
            $like = $driver === 'pgsql' ? 'ilike' : 'like';

            $query->where(function ($q) use ($search, $like) {
                $q->where('nama', $like, "%{$search}%")
                    ->orWhere('status', $like, "%{$search}%")
                    ->orWhere('waktu', $like, "%{$search}%")
                    ->orWhere('catatan', $like, "%{$search}%")
                    ->orWhereHas('santri', fn ($sq) => $sq->where('nama_lengkap', $like, "%{$search}%"))
                    ->orWhereHas('kegiatan', fn ($kq) => $kq->where('kategori', $like, "%{$search}%"));
            });
        }

        if ($mode === 'team') {
            $genderFilter = request()->get('gender_filter', 'all');
            if (in_array($genderFilter, ['putra', 'putri'], true)) {
                $query->whereHas('santri', function ($q) use ($genderFilter) {
                    $q->where('gender', $genderFilter);
                });
            }

            $kategoriFilter = request()->get('kategori_filter', []);
            if (!empty($kategoriFilter)) {
                $query->whereHas('kegiatan', fn ($kq) => $kq->whereIn('kategori', (array)$kategoriFilter));
            }

            $waktuFilter = request()->get('waktu_filter', []);
            if (!empty($waktuFilter)) {
                $query->whereHas('kegiatan', fn ($kq) => $kq->whereIn('waktu', (array)$waktuFilter));
            }

            $statusFilter = request()->get('status_filter', []);
            if (!empty($statusFilter)) {
                $query->whereIn('status', (array)$statusFilter);
            }

            $timFilter = request()->get('tim_filter', []);
            if (!empty($timFilter)) {
                $timNormalized = array_values(array_filter(array_map(fn ($t) => strtolower(trim((string)$t)), (array)$timFilter)));
                $lookup = Santri::teamLookup();
                $codesByTim = [];
                foreach ($lookup as $code => $team) {
                    if ($team !== null && in_array(strtolower($team), $timNormalized, true)) {
                        $codesByTim[] = $code;
                    }
                }
                $query->whereHas('santri', function ($sq) use ($timNormalized, $codesByTim) {
                    $sq->where(function ($inner) use ($timNormalized, $codesByTim) {
                        if (!empty($timNormalized)) {
                            $inner->whereIn(DB::raw('LOWER(tim)'), $timNormalized);
                        }
                        if (!empty($codesByTim)) {
                            $inner->orWhereIn('code', $codesByTim);
                        }
                    });
                });
            }

            $tanggalFilter = request()->get('tanggal');
            if ($tanggalFilter) {
                $query->whereDate('created_at', $tanggalFilter);
            }
        }

        // Different pagination per mode
        $perPage = $mode === 'mine' ? 6 : 11;
        $presensis = $query->paginate($perPage)->withQueryString();
        $santriList = collect();
        if ($isKetertiban) {
            $santriList = Santri::where('gender', $gender)
                ->orderBy('nama_lengkap')
                ->get(['id', 'nama_lengkap', 'tim']);
        }

        // Calculate stats for "mine" mode
        $stats = [
            'total_pertemuan' => 0,
            'hadir' => 0,
            'izin' => 0,
            'sakit' => 0,
            'alpa' => 0,
            'persentase' => 0,
        ];

        $latestUpdates = collect();

        if ($mode === 'mine' && $santriId) {
            // Total pertemuan (count all presensis for this santri)
            $stats['total_pertemuan'] = Presensi::where('santri_id', $santriId)->count();
            
            // Count by status
            $stats['hadir'] = Presensi::where('santri_id', $santriId)->where('status', 'hadir')->count();
            $stats['izin'] = Presensi::where('santri_id', $santriId)->where('status', 'izin')->count();
            $stats['sakit'] = Presensi::where('santri_id', $santriId)->where('status', 'sakit')->count();
            $stats['alpa'] = Presensi::where('santri_id', $santriId)->where('status', 'alpha')->count();
            
            // Calculate percentage
            if ($stats['total_pertemuan'] > 0) {
                $stats['persentase'] = round(($stats['hadir'] / $stats['total_pertemuan']) * 100);
            }

            // Get latest 3 updates
            $latestUpdates = Presensi::with(['kegiatan'])
                ->where('santri_id', $santriId)
                ->latest('created_at')
                ->take(3)
                ->get();
        }

        return view('santri.presensi.index', [
            'presensis' => $presensis,
            'santriList' => $santriList,
            'canManage' => $isKetertiban,
            'canEdit' => $isKetertiban && $mode === 'input',
            'mode' => $mode,
            'gender' => $gender,
            'statuses' => Presensi::STATUS,
            'kategoriOptions' => Kegiatan::KATEGORI,
            'waktuOptions' => Presensi::WAKTU,
            'search' => $search,
            'isStaffViewer' => false,
            'genderFilter' => $mode === 'team' ? request()->get('gender_filter', 'all') : null,
            'kategoriFilter' => $mode === 'team' ? (array)request()->get('kategori_filter', []) : [],
            'waktuFilter' => $mode === 'team' ? (array)request()->get('waktu_filter', []) : [],
            'statusFilter' => $mode === 'team' ? (array)request()->get('status_filter', []) : [],
            'timFilter' => $mode === 'team' ? (array)request()->get('tim_filter', []) : [],
            'tanggalFilter' => $mode === 'team' ? request()->get('tanggal') : null,
            'stats' => $stats,
            'latestUpdates' => $latestUpdates,
        ]);
    }

    public function create(): View
    {
        $this->ensureAllowedRole();
        $this->authorize('create', Presensi::class);

        $user = auth()->user();
        $isKetertiban = $user->isKetertiban();

        // Gender filter for ketertiban (putra/putri/all)
        $gender = request()->get('gender', 'putra');
        if (!in_array($gender, ['putra','putri','all'], true)) {
            $gender = 'putra';
        }

        // Ambil semua santri per gender langsung dari DB
        $santriPaginated = collect();
        if ($isKetertiban) {
            $santriPaginated = Santri::query()
                ->when($gender !== 'all', fn ($q) => $q->where('gender', $gender))
                ->orderBy('nama_lengkap')
                ->select(['id', 'nama_lengkap', 'tim', 'code'])
                ->paginate(100)
                ->withQueryString();
        }

        // Stats awal nol, akan berubah live sesuai pilihan
        $stats = [
            'hadir' => 0,
            'izin' => 0,
            'sakit' => 0,
            'alpha' => 0,
        ];

        return view('santri.presensi.create', [
            'santriList' => $santriPaginated,
            'canManage' => $isKetertiban,
            'gender' => $gender,
            'statuses' => Presensi::STATUS,
            'kategoriOptions' => Kegiatan::KATEGORI,
            'waktuOptions' => Presensi::WAKTU,
            'stats' => $stats,
        ]);
    }

    public function store(StorePresensiRequest $request): RedirectResponse
    {
        $this->ensureSantriRole();
        $this->authorize('create', Presensi::class);

        $data = $request->validated();

        $tanggal = Carbon::parse($data['tanggal'])->startOfDay();

        // Batch mode: presensi[santri_id] => status
        if (!empty($data['presensi'] ?? [])) {
            $kegiatan = Kegiatan::firstOrCreate(
                ['kategori' => $data['kategori'], 'waktu' => $data['waktu']],
                ['catatan' => null]
            );

            $santriIds = array_keys($data['presensi']);
            $santriList = Santri::whereIn('id', $santriIds)->get(['id','nama_lengkap']);

            foreach ($santriList as $santri) {
                $status = $data['presensi'][$santri->id] ?? null;
                if (! $status) {
                    continue;
                }

                $p = new Presensi();
                $p->santri_id = $santri->id;
                $p->nama = $santri->nama_lengkap;
                $p->status = $status;
                $p->kegiatan_id = $kegiatan->id;
                $p->catatan = $data['catatan'] ?? null;
                $p->waktu = $data['waktu'];
                $p->created_at = $tanggal;
                $p->updated_at = $tanggal;
                $p->timestamps = false;
                $p->save();
            }

            return back()->with('success', 'Presensi batch berhasil disimpan');
        }

        // Single mode (fallback)
        $santri = Santri::findOrFail($data['santri_id']);

        $kegiatan = Kegiatan::firstOrCreate(
            ['kategori' => $data['kategori'], 'waktu' => $data['waktu']],
            ['catatan' => null]
        );

        $p = new Presensi();
        $p->santri_id = $santri->id;
        $p->nama = $data['nama'] ?? $santri->nama_lengkap;
        $p->status = $data['status'];
        $p->kegiatan_id = $kegiatan->id;
        $p->catatan = $data['catatan'] ?? null;
        $p->waktu = $data['waktu'];
        $p->created_at = $tanggal;
        $p->updated_at = $tanggal;
        $p->timestamps = false;
        $p->save();

        return back()->with('success', 'Presensi berhasil ditambahkan');
    }

    public function show(Presensi $presensi): View
    {
        $this->ensureSantriRole();
        $this->authorize('view', $presensi);

        $presensi->load(['santri', 'kegiatan']);

        return view('santri.presensi.show', compact('presensi'));
    }

    public function update(UpdatePresensiRequest $request, Presensi $presensi): RedirectResponse
    {
        $this->ensureSantriRole();
        $this->authorize('update', $presensi);

        $data = $request->validated();

        if (isset($data['santri_id'])) {
            $santri = Santri::findOrFail($data['santri_id']);
            $presensi->santri_id = $santri->id;
            $presensi->nama = $data['nama'] ?? $santri->nama_lengkap;
        }

        if (isset($data['kategori']) || isset($data['waktu'])) {
            $kategori = $data['kategori'] ?? $presensi->kegiatan->kategori;
            $waktu = $data['waktu'] ?? $presensi->waktu;
            $kegiatan = Kegiatan::firstOrCreate(['kategori' => $kategori, 'waktu' => $waktu]);
            $presensi->kegiatan_id = $kegiatan->id;
            $presensi->waktu = $waktu;
        }

        if (isset($data['status'])) {
            $presensi->status = $data['status'];
        }

        if (array_key_exists('catatan', $data)) {
            $presensi->catatan = $data['catatan'];
        }

        if (isset($data['nama']) && ! isset($data['santri_id'])) {
            $presensi->nama = $data['nama'];
        }

        $presensi->save();

        return back()->with('success', 'Presensi berhasil diperbarui');
    }

    public function destroy(Presensi $presensi): RedirectResponse
    {
        $this->ensureSantriRole();
        $this->authorize('delete', $presensi);

        $presensi->delete();

        return back()->with('success', 'Presensi berhasil dihapus');
    }
}
