<?php

namespace App\Http\Controllers\Santri;

use App\Enum\Role;
use App\Http\Controllers\Controller;
use App\Models\ProgressKeilmuan;
use App\Models\Santri;
use App\Models\User as UserModel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ProgressKeilmuanController extends Controller
{
    private const CATEGORY_QURAN = 'al-quran';
    private const CATEGORY_HADITS = 'al-hadits';
    private const PROGRESS_PERCENT_SQL = "CASE WHEN target > 0 THEN CASE WHEN ROUND((capaian * 100.0) / target) > 100 THEN 100 ELSE ROUND((capaian * 100.0) / target) END ELSE 0 END";

    private function wantsAjax(Request $request): bool
    {
        return $request->ajax() || $request->expectsJson() || $request->boolean('ajax');
    }

    public function index(Request $request): View|RedirectResponse
    {
        $user = auth()->user();
        if ($user?->role === Role::WALI) {
            $firstChildCode = $user->waliOf()
                ->orderBy('santris.nama_lengkap')
                ->value('santris.code');

            if (filled($firstChildCode)) {
                return redirect()->route('wali.anak.progres', ['santriCode' => $firstChildCode]);
            }

            return redirect()->route('profile.edit')->with('status', 'Akun wali belum terhubung ke data anak.');
        }

        if (in_array($user?->role, [Role::DEWAN_GURU, Role::PENGURUS], true)) {
            return $this->staffIndex($request);
        }

        $santri = $this->requireSantri();
        $category = $this->resolveCategory((string) $request->input('category'));

        $modules = collect($this->modules($category));

        $records = ProgressKeilmuan::where('santri_id', $santri->id)
            ->where('level', $category)
            ->get()
            ->keyBy('judul');

        $items = $modules->mapWithKeys(function (array $entry) use ($records) {
            $record = $records->get($entry['judul'] ?? $entry[0] ?? null);
            $title = $entry['judul'] ?? $entry[0] ?? '';
            $target = $entry['target'] ?? 0;
            $value = $record?->capaian;
            $percent = $value !== null && $target > 0 ? (int) min(100, round(($value / $target) * 100)) : 0;

            return [$title => [
                'judul' => $title,
                'target' => $target,
                'value' => $value,
                'persentase' => $percent,
                'updated_at' => $record?->updated_at,
            ]];
        });

        $stats = $this->buildStats($items);

        $recentUpdates = ProgressKeilmuan::where('santri_id', $santri->id)
            ->where('level', $category)
            ->latest('updated_at')
            ->take(5)
            ->get();

        return view('santri.pages.data.progres', [
            'santri' => $santri,
            'category' => $category,
            'items' => $items,
            'modules' => $modules,
            'stats' => $stats,
            'recentUpdates' => $recentUpdates,
        ]);
    }

    public function sync(Request $request): RedirectResponse
    {
        $santri = $this->requireSantri();
        $category = $this->resolveCategory((string) $request->input('category'));
        $modules = collect($this->modules($category))->keyBy('judul');

        $valueRules = ['nullable', 'integer', 'min:0'];
        if ($category === self::CATEGORY_QURAN) {
            $valueRules[] = 'max:20';
        }

        $payload = $request->validate([
            'category' => ['required', 'in:' . self::CATEGORY_QURAN . ',' . self::CATEGORY_HADITS],
            'modules' => ['required', 'array'],
            'modules.*.judul' => ['required', 'string'],
            'modules.*.value' => $valueRules,
        ]);

        $errors = [];
        foreach ($payload['modules'] as $row) {
            if (! $modules->has($row['judul'])) {
                continue;
            }

            $target = $modules[$row['judul']]['target'];
            $value = $row['value'] ?? null;

            if ($value !== null && $value !== '' && $value > $target) {
                $errors[] = 'Progres ' . $row['judul'] . ' maksimal ' . $target . ' halaman.';
            }
        }

        if (! empty($errors)) {
            return back()->withErrors($errors)->withInput();
        }

        $existing = ProgressKeilmuan::where('santri_id', $santri->id)
            ->where('level', $category)
            ->get()
            ->keyBy('judul');

        $timestamp = now(config('app.timezone'));

        $this->authorize('create', ProgressKeilmuan::class);

        foreach ($payload['modules'] as $row) {
            if (! $modules->has($row['judul'])) {
                continue;
            }

            $value = $row['value'] ?? null;
            $target = $modules[$row['judul']]['target'];
            $record = $existing->get($row['judul']);

            if ($value === null || $value === '') {
                if ($record) {
                    $this->authorize('delete', $record);
                    $record->delete();
                }
                continue;
            }

            $value = (int) $value;

            if ($record) {
                $this->authorize('update', $record);
                $record->update([
                    'capaian' => $value,
                    'target' => $target,
                    'satuan' => 'halaman',
                    'level' => $category,
                    'judul' => $row['judul'],
                    'terakhir_setor' => $timestamp,
                ]);
            } else {
                ProgressKeilmuan::create([
                    'santri_id' => $santri->id,
                    'judul' => $row['judul'],
                    'target' => $target,
                    'capaian' => $value,
                    'satuan' => 'halaman',
                    'level' => $category,
                    'terakhir_setor' => $timestamp,
                ]);
            }
        }

        return back()->with('success', 'Progres keilmuan berhasil diperbarui.');
    }

    public function detail(Request $request, string $santriCode): View
    {
        abort_unless(
            in_array(auth()->user()?->role, [Role::DEWAN_GURU, Role::PENGURUS], true),
            403
        );

        $santri = Santri::query()
            ->with(['kelas', 'user'])
            ->where('code', $santriCode)
            ->firstOrFail();

        $items = ProgressKeilmuan::query()
            ->where('santri_id', $santri->id)
            ->latest('updated_at')
            ->get();

        return view('wali.pages.data.progres', [
            'santri' => $santri,
            'items' => $items,
            'isStaffView' => true,
            'backUrl' => route('santri.data.progres', $request->only(['category', 'gender', 'q', 'page'])),
        ]);
    }

    private function staffIndex(Request $request): View|JsonResponse
    {
        $category = $this->resolveCategory((string) $request->input('category'));
        $genderFilter = strtolower(trim((string) $request->input('gender', 'all')));
        if (! in_array($genderFilter, ['all', 'putra', 'putri'], true)) {
            $genderFilter = 'all';
        }
        $modules = collect($this->modules($category));
        $moduleCount = $modules->count();

        $progressAggregate = ProgressKeilmuan::query()
            ->where('level', $category)
            ->select('santri_id')
            ->selectRaw("SUM(CASE WHEN " . self::PROGRESS_PERCENT_SQL . " >= 100 THEN 1 ELSE 0 END) as completed")
            ->selectRaw("SUM(CASE WHEN " . self::PROGRESS_PERCENT_SQL . " > 0 AND " . self::PROGRESS_PERCENT_SQL . " < 100 THEN 1 ELSE 0 END) as in_progress")
            ->selectRaw("ROUND(COALESCE(AVG(" . self::PROGRESS_PERCENT_SQL . "), 0)) as average")
            ->selectRaw('MAX(COALESCE(terakhir_setor, updated_at)) as updated_at')
            ->groupBy('santri_id');

        $searchQuery = trim((string) $request->input('q', ''));
        $driver = DB::connection()->getDriverName();
        $like = $driver === 'pgsql' ? 'ilike' : 'like';

        $applyFilters = function ($query) use ($genderFilter, $searchQuery, $like) {
            if ($genderFilter !== 'all') {
                $query->where('santris.gender', $genderFilter);
            }

            if ($searchQuery !== '') {
                $query->where(function ($q) use ($searchQuery, $like) {
                    $q->where('santris.nama_lengkap', $like, "%{$searchQuery}%")
                        ->orWhere('santris.code', $like, "%{$searchQuery}%")
                        ->orWhere('santris.tim', $like, "%{$searchQuery}%")
                        ->orWhere('kelas.nama', $like, "%{$searchQuery}%");
                });
            }
        };

        $statsQuery = Santri::query()
            ->leftJoinSub($progressAggregate, 'progress_agg', fn ($join) => $join->on('progress_agg.santri_id', '=', 'santris.id'))
            ->leftJoin('kelas', 'kelas.id', '=', 'santris.kelas_id');

        $applyFilters($statsQuery);

        $statsRow = $statsQuery
            ->selectRaw('COUNT(*) as total_santri')
            ->selectRaw('SUM(CASE WHEN (COALESCE(progress_agg.completed, 0) + COALESCE(progress_agg.in_progress, 0)) > 0 THEN 1 ELSE 0 END) as active_santri')
            ->selectRaw('ROUND(COALESCE(AVG(COALESCE(progress_agg.average, 0)), 0)) as average')
            ->selectRaw('SUM(COALESCE(progress_agg.completed, 0)) as completed_modules')
            ->first();

        $rowsQuery = Santri::query()
            ->leftJoinSub($progressAggregate, 'progress_agg', fn ($join) => $join->on('progress_agg.santri_id', '=', 'santris.id'))
            ->leftJoin('kelas', 'kelas.id', '=', 'santris.kelas_id');

        $applyFilters($rowsQuery);

        $rowsPage = $rowsQuery
            ->select('santris.id', 'santris.code')
            ->selectRaw("COALESCE(santris.nama_lengkap, '-') as nama")
            ->selectRaw("COALESCE(kelas.nama, '-') as kelas")
            ->selectRaw("COALESCE(santris.tim, '') as tim")
            ->selectRaw("COALESCE(santris.gender, '') as gender_raw")
            ->selectRaw('COALESCE(progress_agg.completed, 0) as completed')
            ->selectRaw('COALESCE(progress_agg.in_progress, 0) as in_progress')
            ->selectRaw('COALESCE(progress_agg.average, 0) as average')
            ->selectRaw('progress_agg.updated_at as updated_at')
            ->orderByDesc('average')
            ->orderBy('santris.nama_lengkap')
            ->paginate(8)
            ->withQueryString()
            ->through(function ($row): array {
                $code = (string) ($row->code ?? '');
                $teamName = trim((string) ($row->tim ?? ''));
                if ($teamName === '' && $code !== '') {
                    $teamName = (string) (Santri::teamFromCode($code) ?? '');
                }

                return [
                    'id' => (int) ($row->id ?? 0),
                    'code' => $code,
                    'nama' => (string) ($row->nama ?? '-'),
                    'gender' => $this->normalizeGender((string) ($row->gender_raw ?? '')),
                    'kelas' => (string) ($row->kelas ?? '-'),
                    'tim' => $teamName !== '' ? $teamName : '-',
                    'tim_badge' => UserModel::teamAbbreviation($teamName),
                    'completed' => (int) ($row->completed ?? 0),
                    'in_progress' => (int) ($row->in_progress ?? 0),
                    'average' => (int) ($row->average ?? 0),
                    'updated_at' => $row->updated_at,
                ];
            });

        $stats = [
            'totalSantri' => (int) ($statsRow?->total_santri ?? 0),
            'activeSantri' => (int) ($statsRow?->active_santri ?? 0),
            'average' => (int) ($statsRow?->average ?? 0),
            'completedModules' => (int) ($statsRow?->completed_modules ?? 0),
            'moduleCount' => $moduleCount,
        ];

        $viewData = [
            'category' => $category,
            'rows' => $rowsPage,
            'stats' => $stats,
            'searchQuery' => $searchQuery,
            'genderFilter' => $genderFilter,
        ];

        if ($this->wantsAjax($request)) {
            return response()->json([
                'html' => view('santri.pages.data.partials.progres-staff-table-panel', $viewData)->render(),
            ]);
        }

        return view('santri.pages.data.progres-staff', $viewData);
    }

    private function normalizeGender(string $value): string
    {
        $gender = strtolower(trim($value));
        if (in_array($gender, ['putra', 'l', 'lk', 'laki-laki', 'laki', 'male', 'ikhwan'], true)) {
            return 'putra';
        }

        if (in_array($gender, ['putri', 'p', 'pr', 'perempuan', 'female', 'akhwat'], true)) {
            return 'putri';
        }

        return '';
    }

    private function buildStats(Collection $items): array
    {
        $total = $items->count();
        $avg = $total ? (int) round($items->avg(fn ($item) => $item['persentase'])) : 0;

        return [
            'total' => $total,
            'completed' => $items->filter(fn ($item) => ($item['persentase'] ?? 0) >= 100)->count(),
            'inProgress' => $items->filter(fn ($item) => ($item['persentase'] ?? 0) > 0 && ($item['persentase'] ?? 0) < 100)->count(),
            'average' => $avg,
        ];
    }

    private function resolveCategory(?string $category): string
    {
        return in_array($category, [self::CATEGORY_QURAN, self::CATEGORY_HADITS], true)
            ? $category
            : self::CATEGORY_QURAN;
    }

    private function modules(string $category): array
    {
        return $category === self::CATEGORY_HADITS
            ? $this->haditsList()
            : $this->quranList();
    }

    private function quranList(): array
    {
        return array_map(fn ($i) => ['judul' => 'Juz ' . $i, 'target' => 20], range(1, 30));
    }

    private function haditsList(): array
    {
        return [
            ["judul" => "Mukhtaru Da'awat", 'target' => 171],
            ['judul' => 'Buku Saku Tata Krama', 'target' => 60],
            ['judul' => 'Al Khulasoh Fii Adabith Tholib', 'target' => 20],
            ['judul' => 'Materi Kelas Bacaan', 'target' => 37],
            ['judul' => 'K. Sholah', 'target' => 151],
            ["judul" => "Al-Khulashoh Fil Imla'", 'target' => 20],
            ["judul" => "Luzumul Jama'ah", 'target' => 40],
            ['judul' => 'Materi Kelas Pegon', 'target' => 30],
            ['judul' => 'K. Sholatin Nawafil', 'target' => 98],
            ['judul' => 'K. Shoum', 'target' => 98],
            ['judul' => 'Materi Kelas Lambatan', 'target' => 47],
            ["judul" => "K. Da'awat", 'target' => 65],
            ['judul' => 'K. Adab', 'target' => 95],
            ['judul' => 'K. Shifatil Jannati Wannar', 'target' => 85],
            ['judul' => 'K. Janaiz', 'target' => 79],
            ['judul' => 'K. Adillah', 'target' => 96],
            ['judul' => 'K. Manasik wal Jihad', 'target' => 51],
            ['judul' => 'Materi Kelas Cepatan', 'target' => 70],
            ['judul' => 'K. Haji', 'target' => 111],
            ['judul' => 'K. Manaskil Haji', 'target' => 116],
            ['judul' => 'K. Ahkam', 'target' => 124],
            ['judul' => 'K. Jihad', 'target' => 63],
            ['judul' => 'K. Imaroh', 'target' => 104],
            ['judul' => 'K. Imaroh min Kanzil ummal', 'target' => 122],
            ['judul' => 'Khutbah', 'target' => 152],
            ['judul' => 'Materi Kelas Saringan', 'target' => 63],
            ['judul' => 'Hidayatul Mustafidz Fit-Tajwid', 'target' => 98],
            ['judul' => 'K. Nikah', 'target' => 101],
            ['judul' => 'K. Faroidh', 'target' => 134],
            ['judul' => 'Syarah Asmaullohul Husna', 'target' => 39],
            ["judul" => "Syarah Do'a ASAD", 'target' => 35],
        ];
    }

    private function requireSantri()
    {
        $santri = auth()->user()?->santri;
        abort_unless($santri, 403, 'Hanya santri yang dapat mengelola progres keilmuan.');

        return $santri;
    }

}
