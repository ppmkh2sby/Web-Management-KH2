<?php

namespace App\Http\Controllers\Santri;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreKafarahRequest;
use App\Http\Requests\UpdateKafarahRequest;
use App\Models\Kafarah;
use App\Models\Santri;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class KafarahController extends Controller
{
    private function ensureAllowedRole(): void
    {
        abort_unless(auth()->check(), 403);

        $role = auth()->user()->role;
        $allowed = [\App\Enum\Role::SANTRI];

        abort_unless(in_array($role, $allowed, true) || auth()->user()->isKetertiban(), 403);
    }

    private function ensureSantriRole(): void
    {
        $this->ensureAllowedRole();
    }

    public function index(): View
    {
        $this->ensureAllowedRole();
        $this->authorize('viewAny', Kafarah::class);

        $user = auth()->user();
        $isKetertiban = $user->isKetertiban();
        $santriId = $user->santri?->id;

        $mode = request()->get('mode', $isKetertiban ? 'team' : 'mine');
        if (!in_array($mode, ['mine', 'team'], true)) {
            $mode = $isKetertiban ? 'team' : 'mine';
        }

        // Paksa santri non-KTB hanya ke mode "mine"
        if ($user->role === \App\Enum\Role::SANTRI && ! $isKetertiban) {
            $mode = 'mine';
        }

        $query = Kafarah::with(['santri'])->latest('updated_at');

        if ($mode === 'mine' && $user->role === \App\Enum\Role::SANTRI) {
            abort_unless($santriId, 403);
            $query->where('santri_id', $santriId);

            // Add filters for mine mode
            $statusFilterMine = request()->get('status_filter_mine', []);
            if (!empty($statusFilterMine)) {
                $query->whereIn('status', (array)$statusFilterMine);
            }

            $tanggalFilterMine = request()->get('tanggal_mine');
            if ($tanggalFilterMine) {
                $query->whereDate('tanggal', $tanggalFilterMine);
            }

            $bulanFilterMine = request()->get('bulan_mine');
            if ($bulanFilterMine) {
                $query->whereYear('tanggal', substr($bulanFilterMine, 0, 4))
                      ->whereMonth('tanggal', substr($bulanFilterMine, 5, 2));
            }

            // Search filter for mine mode
            $searchMine = trim((string) request()->get('search_mine', ''));
            if ($searchMine !== '') {
                $query->where(function ($q) use ($searchMine) {
                    $q->where('kegiatan', 'like', "%{$searchMine}%")
                        ->orWhere('keterangan', 'like', "%{$searchMine}%");
                });
            }
        } elseif ($mode === 'team') {
            // Show all santri - accessible by ketertiban
        } else {
            abort(403);
        }

        $search = trim((string) request()->get('search', ''));
        if ($search !== '') {
            $driver = DB::connection()->getDriverName();
            $like = $driver === 'pgsql' ? 'ilike' : 'like';

            $query->where(function ($q) use ($search, $like) {
                $q->where('kegiatan', $like, "%{$search}%")
                    ->orWhere('status', $like, "%{$search}%")
                    ->orWhere('keterangan', $like, "%{$search}%")
                    ->orWhereHas('santri', fn ($sq) => $sq->where('nama_lengkap', $like, "%{$search}%"));
            });
        }

        if ($mode === 'team') {
            $genderFilter = request()->get('gender_filter', 'all');
            if (in_array($genderFilter, ['putra', 'putri'], true)) {
                $query->whereHas('santri', function ($q) use ($genderFilter) {
                    $q->where('gender', $genderFilter);
                });
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
                $query->whereDate('tanggal', $tanggalFilter);
            }
        }

        // Different pagination per mode
        $perPage = $mode === 'mine' ? 6 : 11;
        $kafarahs = $query->paginate($perPage)->withQueryString();

        // Calculate stats for "mine" mode
        $stats = [
            'total' => 0,
            'total_kafarah' => 0,
            'jumlah_setor' => 0,
            'tanggungan' => 0,
        ];

        $latestUpdates = collect();

        if ($mode === 'mine' && $santriId) {
            $aggregate = Kafarah::query()
                ->where('santri_id', $santriId)
                ->selectRaw('COUNT(*) as total')
                ->selectRaw('COALESCE(SUM(tanggungan), 0) as total_kafarah')
                ->selectRaw('COALESCE(SUM(jumlah_setor), 0) as jumlah_setor')
                ->first();

            $stats['total'] = (int) ($aggregate?->total ?? 0);
            $stats['total_kafarah'] = (int) ($aggregate?->total_kafarah ?? 0);
            $stats['jumlah_setor'] = (int) ($aggregate?->jumlah_setor ?? 0);
            $stats['tanggungan'] = $stats['total_kafarah'] - $stats['jumlah_setor'];

            // Get latest 3 updates
            $latestUpdates = Kafarah::where('santri_id', $santriId)
                ->latest('tanggal')
                ->take(3)
                ->get();
        }

        return view('santri.kafarah.index', [
            'kafarahs' => $kafarahs,
            'canManage' => $isKetertiban,
            'mode' => $mode,
            'jenisPelanggaranOptions' => Kafarah::JENIS_PELANGGARAN,
            'search' => $search,
            'isStaffViewer' => false,
            'genderFilter' => $mode === 'team' ? request()->get('gender_filter', 'all') : null,
            'timFilter' => $mode === 'team' ? (array)request()->get('tim_filter', []) : [],
            'tanggalFilter' => $mode === 'team' ? request()->get('tanggal') : null,
            'stats' => $stats,
            'latestUpdates' => $latestUpdates,
        ]);
    }

    public function create(): View
    {
        $this->ensureAllowedRole();
        $this->authorize('create', Kafarah::class);

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
            'selected' => 0,
        ];

        return view('santri.kafarah.create', [
            'santriList' => $santriPaginated,
            'canManage' => $isKetertiban,
            'gender' => $gender,
            'jenisPelanggaranOptions' => Kafarah::JENIS_PELANGGARAN,
            'stats' => $stats,
        ]);
    }

    public function store(StoreKafarahRequest $request): RedirectResponse
    {
        $this->ensureSantriRole();
        $this->authorize('create', Kafarah::class);

        $data = $request->validated();
        $tanggal = Carbon::parse($data['tanggal'] ?? now())->startOfDay();
        $jenisPelanggaran = $data['jenis_pelanggaran'];
        $kafarahInfo = Kafarah::getKafarahFromPelanggaran($jenisPelanggaran);
        $tenggat = $tanggal->copy()->addDays(7)->format('Y-m-d');

        $santriList = Santri::whereIn('id', $data['santri_ids'])->get(['id']);

        foreach ($santriList as $santri) {
            Kafarah::create([
                'santri_id' => $santri->id,
                'tanggal' => $tanggal,
                'jenis_pelanggaran' => $jenisPelanggaran,
                'kafarah' => $kafarahInfo['kafarah'],
                'jumlah_setor' => 0,
                'tanggungan' => $kafarahInfo['jumlah'],
                'tenggat' => $tenggat,
            ]);
        }

        return back()->with('success', 'Kafarah berhasil disimpan.');
    }

    public function update(UpdateKafarahRequest $request, Kafarah $kafarah): RedirectResponse
    {
        $this->ensureSantriRole();
        $this->authorize('update', $kafarah);

        $kafarah->update($request->validated());

        return back()->with('success', 'Kafarah berhasil diperbarui.');
    }

    public function destroy(Kafarah $kafarah): RedirectResponse
    {
        $this->ensureSantriRole();
        $this->authorize('delete', $kafarah);

        $kafarah->delete();

        return back()->with('success', 'Kafarah berhasil dihapus.');
    }
}
