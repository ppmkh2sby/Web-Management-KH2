<?php

namespace App\Http\Controllers\Santri;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePresensiRequest;
use App\Http\Requests\UpdatePresensiRequest;
use App\Enum\Role;
use App\Models\Kegiatan;
use App\Models\Presensi;
use App\Models\Santri;
use App\Models\Sesi;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PresensiController extends Controller
{
    private const REKAP_PER_PAGE = 25;

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

    private function wantsAjax(Request $request): bool
    {
        return $request->ajax() || $request->expectsJson() || $request->boolean('ajax');
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

    /**
     * @param array<int, mixed> $requestedKelasIds
     * @return array<int, int>
     */
    private function resolveDegurKelasIds(array $requestedKelasIds): array
    {
        $allowedKelasIds = $this->degurKelasIds();
        abort_if(empty($allowedKelasIds), 403, 'Akun dewan guru belum ditautkan ke kelas ajar.');

        $requested = collect($requestedKelasIds)
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $id > 0)
            ->unique()
            ->values();

        if ($requested->isEmpty()) {
            return $allowedKelasIds;
        }

        $invalid = $requested->diff($allowedKelasIds);
        abort_if($invalid->isNotEmpty(), 403, 'Anda hanya bisa memilih kelas yang Anda ampu.');

        return $requested->all();
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

        $query = Presensi::query()
            ->select([
                'id',
                'santri_id',
                'kegiatan_id',
                'sesi_id',
                'nama',
                'status',
                'waktu',
                'catatan',
                'created_at',
                'updated_at',
            ])
            ->with([
                'santri:id,nama_lengkap,tim,code',
                'kegiatan:id,kategori,waktu',
                'sesi:id,tanggal',
            ])
            ->latest('updated_at');

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
        $presensis = $mode === 'team'
            ? $query->simplePaginate($perPage)->withQueryString()
            : $query->paginate($perPage)->withQueryString();
        $santriList = collect();
        if ($isKetertiban && $mode === 'input') {
            $santriList = Santri::where('gender', $gender)
                ->orderBy('nama_lengkap')
                ->get(['id', 'nama_lengkap', 'tim']);
        }

        // Calculate stats for "mine" mode
        $stats = [
            'total_pertemuan' => 0,
            'total_pertemuan_bulan_ini' => 0,
            'hadir' => 0,
            'izin' => 0,
            'sakit' => 0,
            'alpa' => 0,
            'persentase' => 0,
        ];

        $latestUpdates = collect();

        if ($mode === 'mine' && $santriId) {
            $statusCounts = Presensi::query()
                ->where('santri_id', $santriId)
                ->selectRaw('status, count(*) as total')
                ->groupBy('status')
                ->pluck('total', 'status');

            $legacyPresensiTotal = (int) $statusCounts->sum();

            $santri = Santri::query()->select(['id', 'kelas_id'])->find($santriId);
            if ($santri?->kelas_id) {
                $sesiBaseQuery = Sesi::query()
                    ->whereHas('kelas', fn ($q) => $q->where('kelas.id', $santri->kelas_id));

                $stats['total_pertemuan'] = (clone $sesiBaseQuery)->count();
                $stats['total_pertemuan_bulan_ini'] = (clone $sesiBaseQuery)
                    ->whereYear('tanggal', now()->year)
                    ->whereMonth('tanggal', now()->month)
                    ->count();
            } else {
                // Fallback data lama saat kelas belum terhubung.
                $stats['total_pertemuan'] = $legacyPresensiTotal;
            }
            
            // Count by status from a single grouped query.
            $stats['hadir'] = (int) ($statusCounts['hadir'] ?? 0);
            $stats['izin'] = (int) ($statusCounts['izin'] ?? 0);
            $stats['sakit'] = (int) ($statusCounts['sakit'] ?? 0);
            $stats['alpa'] = (int) ($statusCounts['alpha'] ?? 0);

            // Backward compatibility:
            // jika data historis belum punya sesi, pakai total record presensi lama.
            if ($stats['total_pertemuan'] === 0 && $legacyPresensiTotal > 0) {
                $stats['total_pertemuan'] = $legacyPresensiTotal;
                $stats['total_pertemuan_bulan_ini'] = Presensi::where('santri_id', $santriId)
                    ->whereYear('created_at', now()->year)
                    ->whereMonth('created_at', now()->month)
                    ->count();
            }
            
            // Calculate percentage
            if ($stats['total_pertemuan'] > 0) {
                $stats['persentase'] = round(($stats['hadir'] / $stats['total_pertemuan']) * 100);
            }

            // Get latest 3 updates
            $latestUpdates = Presensi::with(['kegiatan', 'sesi'])
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

    public function rekap(Request $request): View|JsonResponse
    {
        $this->ensureAllowedRole();
        $this->authorize('viewAny', Presensi::class);

        $user = auth()->user();
        abort_unless($user && $user->isKetertiban(), 403, 'Fitur rekap presensi khusus tim KTB.');

        $bulanInput = trim((string) $request->query('bulan', now()->format('Y-m')));
        if (!preg_match('/^\d{4}\-(0[1-9]|1[0-2])$/', $bulanInput)) {
            $bulanInput = now()->format('Y-m');
        }

        [$tahun, $bulan] = array_map('intval', explode('-', $bulanInput));
        $monthStart = Carbon::createFromDate($tahun, $bulan, 1)->startOfMonth();
        $monthEnd = $monthStart->copy()->endOfMonth();
        $monthStartDate = $monthStart->toDateString();
        $monthEndDate = $monthEnd->toDateString();
        $legacyStart = $monthStart->copy()->startOfDay();
        $legacyEnd = $monthEnd->copy()->endOfDay();

        $kategori = strtolower(trim((string) $request->query('kategori', 'all')));
        if ($kategori !== 'all' && !in_array($kategori, Kegiatan::KATEGORI, true)) {
            $kategori = 'all';
        }

        $waktu = strtolower(trim((string) $request->query('waktu', 'all')));
        if ($waktu !== 'all' && !in_array($waktu, Presensi::WAKTU, true)) {
            $waktu = 'all';
        }

        $activeTab = strtolower(trim((string) $request->query('tab', 'putra')));
        if (!in_array($activeTab, ['putra', 'putri'], true)) {
            $activeTab = 'putra';
        }

        $monthlyPresensiIds = Presensi::query()
            ->select('presensis.id')
            ->join('sesi', 'sesi.id', '=', 'presensis.sesi_id')
            ->whereBetween('sesi.tanggal', [$monthStartDate, $monthEndDate])
            ->unionAll(
                Presensi::query()
                    ->select('presensis.id')
                    ->whereNull('presensis.sesi_id')
                    ->whereBetween('presensis.created_at', [$legacyStart, $legacyEnd])
            );

        $recordsQuery = Presensi::query()
            ->joinSub($monthlyPresensiIds, 'monthly_presensis', function ($join) {
                $join->on('monthly_presensis.id', '=', 'presensis.id');
            })
            ->join('santris', 'santris.id', '=', 'presensis.santri_id')
            ->leftJoin('kegiatans', 'kegiatans.id', '=', 'presensis.kegiatan_id')
            ->whereIn('santris.gender', ['putra', 'putri']);

        if ($kategori !== 'all') {
            $recordsQuery->where('kegiatans.kategori', $kategori);
        }

        if ($waktu !== 'all') {
            $recordsQuery->where(function ($q) use ($waktu) {
                $q->where('presensis.waktu', $waktu)
                    ->orWhere('kegiatans.waktu', $waktu);
            });
        }

        $summariesByGender = (clone $recordsQuery)
            ->selectRaw('santris.gender as gender')
            ->selectRaw('COUNT(*) as total_input')
            ->selectRaw('COUNT(DISTINCT presensis.santri_id) as total_santri')
            ->selectRaw('COUNT(DISTINCT presensis.sesi_id) as total_sesi')
            ->selectRaw("SUM(CASE WHEN presensis.status = 'hadir' THEN 1 ELSE 0 END) as hadir")
            ->selectRaw("SUM(CASE WHEN presensis.status = 'izin' THEN 1 ELSE 0 END) as izin")
            ->selectRaw("SUM(CASE WHEN presensis.status = 'sakit' THEN 1 ELSE 0 END) as sakit")
            ->selectRaw("SUM(CASE WHEN presensis.status = 'alpha' THEN 1 ELSE 0 END) as alpha")
            ->groupBy('santris.gender')
            ->get()
            ->keyBy(fn ($row) => (string) $row->gender);

        $normalizeSummary = function ($summary): array {
            $totalInput = (int) ($summary?->total_input ?? 0);
            $hadir = (int) ($summary?->hadir ?? 0);

            return [
                'total_santri' => (int) ($summary?->total_santri ?? 0),
                'total_sesi' => (int) ($summary?->total_sesi ?? 0),
                'total_input' => $totalInput,
                'hadir' => $hadir,
                'izin' => (int) ($summary?->izin ?? 0),
                'sakit' => (int) ($summary?->sakit ?? 0),
                'alpha' => (int) ($summary?->alpha ?? 0),
                'persentase' => $totalInput > 0 ? (int) round(($hadir / $totalInput) * 100) : 0,
            ];
        };

        $putraSummary = $normalizeSummary($summariesByGender->get('putra'));
        $putriSummary = $normalizeSummary($summariesByGender->get('putri'));

        $activeRows = (clone $recordsQuery)
            ->where('santris.gender', $activeTab)
            ->selectRaw('presensis.santri_id as santri_id')
            ->selectRaw('santris.nama_lengkap as nama_lengkap')
            ->selectRaw('santris.tim as tim')
            ->selectRaw('santris.code as code')
            ->selectRaw('COUNT(*) as total_input')
            ->selectRaw("SUM(CASE WHEN presensis.status = 'hadir' THEN 1 ELSE 0 END) as hadir")
            ->selectRaw("SUM(CASE WHEN presensis.status = 'izin' THEN 1 ELSE 0 END) as izin")
            ->selectRaw("SUM(CASE WHEN presensis.status = 'sakit' THEN 1 ELSE 0 END) as sakit")
            ->selectRaw("SUM(CASE WHEN presensis.status = 'alpha' THEN 1 ELSE 0 END) as alpha")
            ->groupBy('presensis.santri_id', 'santris.nama_lengkap', 'santris.tim', 'santris.code')
            ->orderByRaw("CASE WHEN COUNT(*) > 0 THEN ROUND((SUM(CASE WHEN presensis.status = 'hadir' THEN 1 ELSE 0 END) * 100.0) / COUNT(*)) ELSE 0 END DESC")
            ->orderByDesc('hadir')
            ->simplePaginate(self::REKAP_PER_PAGE)
            ->withQueryString()
            ->through(function ($row): array {
                $totalInput = (int) ($row->total_input ?? 0);
                $hadir = (int) ($row->hadir ?? 0);
                $code = (string) ($row->code ?? '');
                $tim = trim((string) ($row->tim ?? ''));
                if ($tim === '' && $code !== '') {
                    $tim = (string) (Santri::teamFromCode($code) ?? '');
                }

                return [
                    'santri_id' => (int) ($row->santri_id ?? 0),
                    'nama_lengkap' => (string) ($row->nama_lengkap ?? '-'),
                    'tim' => $tim !== '' ? $tim : '-',
                    'total_input' => $totalInput,
                    'hadir' => $hadir,
                    'izin' => (int) ($row->izin ?? 0),
                    'sakit' => (int) ($row->sakit ?? 0),
                    'alpha' => (int) ($row->alpha ?? 0),
                    'persentase' => $totalInput > 0 ? (int) round(($hadir / $totalInput) * 100) : 0,
                ];
            });

        $viewData = [
            'bulanInput' => $bulanInput,
            'activeTab' => $activeTab,
            'selectedKategori' => $kategori,
            'selectedWaktu' => $waktu,
            'kategoriOptions' => Kegiatan::KATEGORI,
            'waktuOptions' => Presensi::WAKTU,
            'putraSummary' => $putraSummary,
            'putriSummary' => $putriSummary,
            'activeRows' => $activeRows,
        ];

        if ($this->wantsAjax($request)) {
            return response()->json([
                'html' => view('santri.presensi.partials.rekap-layout', $viewData)->render(),
            ]);
        }

        return view('santri.presensi.rekap', $viewData);
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
        $selectedKelasIds = [];
        $kelasNameMap = [];

        if ($isDegur) {
            $managedKelas = $user->kelasAjar()
                ->orderBy('nama')
                ->get(['kelas.id', 'nama']);
            abort_if($managedKelas->isEmpty(), 403, 'Akun dewan guru belum ditautkan ke kelas ajar.');

            $kelasNameMap = $managedKelas->pluck('nama', 'id')->all();
            $defaultKelasIds = array_map('intval', array_keys($kelasNameMap));
            $requestedKelasIds = (array) request()->input('kelas_ids', $defaultKelasIds);
            $selectedKelasIds = $this->resolveDegurKelasIds($requestedKelasIds);
        }

        $santriQuery = Santri::query()
            ->orderBy('nama_lengkap')
            ->select(['id', 'nama_lengkap', 'tim', 'code', 'kelas_id']);

        if ($isKetertiban) {
            $santriQuery->when($gender !== 'all', fn ($q) => $q->where('gender', $gender));
        } elseif ($isDegur) {
            $santriQuery->whereIn('kelas_id', $selectedKelasIds);
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
            'selectedKelasIds' => $selectedKelasIds,
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

        $user = auth()->user();
        abort_unless($user, 403);

        $isKetertiban = $user->isKetertiban();
        $isDegur = $user->role === Role::DEWAN_GURU;

        $data = $request->validated();
        $kelasIds = [];

        $santriQuery = Santri::query()->orderBy('nama_lengkap');

        if ($isDegur) {
            $kelasIds = $this->resolveDegurKelasIds((array) ($data['kelas_ids'] ?? []));
            abort_if(empty($kelasIds), 422, 'Pilih minimal satu kelas untuk membuat sesi presensi.');
            $santriQuery->whereIn('kelas_id', $kelasIds);
        } elseif ($isKetertiban) {
            $genderScope = $data['gender_scope'] ?? 'putra';
            if (!in_array($genderScope, ['putra', 'putri', 'all'], true)) {
                $genderScope = 'putra';
            }

            $santriQuery->when($genderScope !== 'all', fn ($q) => $q->where('gender', $genderScope));
        } else {
            // fallback mode lama (single input)
            abort_if(empty($data['santri_id'] ?? null), 422, 'Santri tidak valid untuk input presensi.');
            $this->assertWritableSantriIds([(int) $data['santri_id']]);
            $santriQuery->where('id', (int) $data['santri_id']);
        }

        $santriList = $santriQuery->get(['id', 'nama_lengkap']);
        abort_if($santriList->isEmpty(), 422, 'Tidak ada santri dalam sesi presensi ini.');

        $inputPresensi = (array) ($data['presensi'] ?? []);

        if ($isDegur || $isKetertiban) {
            $missingCount = $santriList->filter(function (Santri $santri) use ($inputPresensi): bool {
                $status = $inputPresensi[$santri->id] ?? null;
                return !in_array($status, Presensi::STATUS, true);
            })->count();

            if ($missingCount > 0) {
                throw ValidationException::withMessages([
                    'presensi' => "Masih ada {$missingCount} santri yang belum dipilih status kehadirannya.",
                ]);
            }
        }

        $tanggal = Carbon::parse($data['tanggal'])->startOfDay();
        DB::transaction(function () use ($data, $tanggal, $kelasIds, $santriList, $inputPresensi): void {
            $kegiatan = Kegiatan::firstOrCreate(
                ['kategori' => $data['kategori'], 'waktu' => $data['waktu']],
                ['catatan' => null]
            );

            $sesi = Sesi::create([
                'kegiatan_id' => $kegiatan->id,
                'tanggal' => $tanggal->toDateString(),
                'catatan' => $data['catatan'] ?? null,
            ]);

            $sesi->kelas()->sync($kelasIds);

            foreach ($santriList as $santri) {
                $status = (string) ($inputPresensi[$santri->id] ?? ($data['status'] ?? 'alpha'));
                if (!in_array($status, Presensi::STATUS, true)) {
                    $status = 'alpha';
                }

                $presensi = new Presensi();
                $presensi->santri_id = $santri->id;
                $presensi->nama = $data['nama'] ?? $santri->nama_lengkap;
                $presensi->status = $status;
                $presensi->kegiatan_id = $kegiatan->id;
                $presensi->sesi_id = $sesi->id;
                $presensi->catatan = $data['catatan'] ?? null;
                $presensi->waktu = $data['waktu'];
                $presensi->created_at = $tanggal;
                $presensi->updated_at = $tanggal;
                $presensi->timestamps = false;
                $presensi->save();
            }
        });

        return back()->with('success', 'Presensi sesi berhasil disimpan.');
    }

    public function show(Presensi $presensi): View
    {
        $this->ensureSantriRole();
        $this->authorize('view', $presensi);

        $presensi->load(['santri', 'kegiatan', 'sesi']);

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
