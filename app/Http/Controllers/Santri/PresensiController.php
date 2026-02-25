<?php

namespace App\Http\Controllers\Santri;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePresensiRequest;
use App\Http\Requests\UpdatePresensiRequest;
use App\Enum\Role;
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
        $allowed = [Role::SANTRI, Role::PENGURUS, Role::DEWAN_GURU];

        abort_unless(in_array($role, $allowed, true) || auth()->user()->isKetertiban(), 403);
    }

    /**
     * Guard for write actions. Role gate happens here, then policy enforces
     * whether the authenticated user can create/update/delete.
     */
    private function ensureSantriRole(): void
    {
        $this->ensureAllowedRole();
    }

    /**
     * @return array<int, int>
     */
    private function degurKelasIds(): array
    {
        $user = auth()->user();

        if (! $user || $user->role !== Role::DEWAN_GURU) {
            return [];
        }

        return $user->kelasAjar()
            ->pluck('kelas.id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    private function canInputClassPresensi(): bool
    {
        $user = auth()->user();
        if (! $user) {
            return false;
        }

        if ($user->isKetertiban()) {
            return true;
        }

        return $user->role === Role::DEWAN_GURU && ! empty($this->degurKelasIds());
    }

    /**
     * Pastikan santri yang diinput sesuai scope writer.
     *
     * Ketertiban boleh semua santri.
     * Dewan guru hanya boleh santri dari kelas yang diampu.
     *
     * @param array<int, int|string> $santriIds
     */
    private function assertWritableSantriIds(array $santriIds): void
    {
        $user = auth()->user();
        abort_unless($user, 403);

        if ($user->isKetertiban()) {
            return;
        }

        if ($user->role === Role::DEWAN_GURU) {
            $kelasIds = $this->degurKelasIds();
            abort_if(empty($kelasIds), 403, 'Akun dewan guru belum ditautkan ke kelas ajar.');

            $requestedIds = collect($santriIds)
                ->map(fn ($id) => (int) $id)
                ->filter(fn ($id) => $id > 0)
                ->unique()
                ->values();

            if ($requestedIds->isEmpty()) {
                return;
            }

            $allowedCount = Santri::query()
                ->whereIn('id', $requestedIds)
                ->whereIn('kelas_id', $kelasIds)
                ->count();

            abort_if($allowedCount !== $requestedIds->count(), 403, 'Anda hanya bisa menginput santri sesuai kelas yang diampu.');
            return;
        }

        abort(403, 'Role ini tidak dapat menginput presensi kelas.');
    }

    public function index(): View
    {
        $this->ensureAllowedRole();
        $this->authorize('viewAny', Presensi::class);

        $user = auth()->user();
        $isKetertiban = $user->isKetertiban();
        $isDegur = $user->role === Role::DEWAN_GURU;
        $isStaffViewer = in_array($user->role, [Role::PENGURUS, Role::DEWAN_GURU], true);
        $santriId = $user->santri?->id;
        $degurKelasIds = $this->degurKelasIds();
        $canInputClass = $this->canInputClassPresensi();

        $mode = request()->get('mode', $isKetertiban ? 'input' : ($isStaffViewer ? 'team' : 'mine'));
        if (!in_array($mode, ['input', 'mine', 'team'], true)) {
            $mode = $isKetertiban ? 'input' : ($isStaffViewer ? 'team' : 'mine');
        }

        // Paksa santri non-KTB hanya ke mode "mine"
        if ($user->role === Role::SANTRI && ! $isKetertiban) {
            $mode = 'mine';
        }
        if ($isStaffViewer) {
            $mode = 'team';
        }

        // Gender filter for ketertiban (putra/putri)
        $gender = request()->get('gender', 'putra');
        if (!in_array($gender, ['putra','putri'], true)) {
            $gender = 'putra';
        }

        $query = Presensi::with(['santri', 'kegiatan'])->latest('updated_at');

        if ($isKetertiban && $mode === 'input') {
            $query->whereHas('santri', function ($q) use ($gender) {
                $q->where('gender', $gender);
            });
        } elseif ($mode === 'mine' && $user->role === Role::SANTRI) {
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
            if ($isDegur) {
                abort_if(empty($degurKelasIds), 403, 'Akun dewan guru belum ditautkan ke kelas ajar.');
                $query->whereHas('santri', fn ($sq) => $sq->whereIn('kelas_id', $degurKelasIds));
            }
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
        $perPage = $mode === 'mine' ? 6 : 10;
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
            'canInput' => $canInputClass,
            'canEdit' => $isKetertiban && $mode === 'input',
            'canTeamFilter' => $mode === 'team' && ($isKetertiban || $isStaffViewer),
            'mode' => $mode,
            'gender' => $gender,
            'statuses' => Presensi::STATUS,
            'kategoriOptions' => Kegiatan::KATEGORI,
            'waktuOptions' => Presensi::WAKTU,
            'search' => $search,
            'isStaffViewer' => $isStaffViewer,
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
        $isDegur = $user->role === Role::DEWAN_GURU;

        // Gender filter for ketertiban (putra/putri/all)
        $gender = request()->get('gender', 'putra');
        if (!in_array($gender, ['putra','putri','all'], true)) {
            $gender = 'putra';
        }

        $managedKelas = collect();
        $selectedKelasId = null;
        $kelasNameMap = [];

        if ($isDegur) {
            $managedKelas = $user->kelasAjar()
                ->orderBy('nama')
                ->get(['kelas.id', 'nama']);
            abort_if($managedKelas->isEmpty(), 403, 'Akun dewan guru belum ditautkan ke kelas ajar.');

            $kelasNameMap = $managedKelas->pluck('nama', 'id')->all();
            $selectedKelasId = (int) request()->integer('kelas_id', (int) $managedKelas->first()->id);
            if (!array_key_exists($selectedKelasId, $kelasNameMap)) {
                $selectedKelasId = (int) $managedKelas->first()->id;
            }
        }

        $santriQuery = Santri::query()
            ->orderBy('nama_lengkap')
            ->select(['id', 'nama_lengkap', 'tim', 'code', 'kelas_id']);

        if ($isKetertiban) {
            $santriQuery->when($gender !== 'all', fn ($q) => $q->where('gender', $gender));
        } elseif ($isDegur) {
            $degurKelasIds = array_keys($kelasNameMap);
            $santriQuery->whereIn('kelas_id', $degurKelasIds);
            if ($selectedKelasId) {
                $santriQuery->where('kelas_id', $selectedKelasId);
            }
        } else {
            abort(403, 'Role ini tidak dapat mengakses form input presensi.');
        }

        $perPage = 100;
        if ($isDegur) {
            $perPage = max((clone $santriQuery)->count(), 1);
        }

        $santriPaginated = $santriQuery
            ->paginate($perPage)
            ->withQueryString();

        // Stats awal nol, akan berubah live sesuai pilihan
        $stats = [
            'hadir' => 0,
            'izin' => 0,
            'sakit' => 0,
            'alpha' => 0,
        ];

        return view('santri.presensi.create', [
            'santriList' => $santriPaginated,
            'canManage' => $isKetertiban || $isDegur,
            'isDegur' => $isDegur,
            'gender' => $gender,
            'managedKelas' => $managedKelas,
            'selectedKelasId' => $selectedKelasId,
            'kelasNameMap' => $kelasNameMap,
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
            $this->assertWritableSantriIds($santriIds);
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
        $this->assertWritableSantriIds([(int) $data['santri_id']]);
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
