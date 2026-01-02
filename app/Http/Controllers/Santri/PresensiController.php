<?php

namespace App\Http\Controllers\Santri;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePresensiRequest;
use App\Http\Requests\UpdatePresensiRequest;
use App\Models\Kegiatan;
use App\Models\Presensi;
use App\Models\Santri;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PresensiController extends Controller
{
    private function ensureAllowedRole(): void
    {
        abort_unless(auth()->check(), 403);

        $role = auth()->user()->role;
        $allowed = [\App\Enum\Role::SANTRI, \App\Enum\Role::PENGURUS, \App\Enum\Role::DEWAN_GURU];

        abort_unless(in_array($role, $allowed, true) || auth()->user()->isKetertiban(), 403);
    }

    public function index(): View
    {
        $this->ensureAllowedRole();
        $this->authorize('viewAny', Presensi::class);

        $user = auth()->user();
        $isKetertiban = $user->isKetertiban();
        $santriId = $user->santri?->id;
        $isStaffViewer = in_array($user->role, [\App\Enum\Role::PENGURUS, \App\Enum\Role::DEWAN_GURU], true);

        $mode = request()->get('mode', $isKetertiban ? 'input' : ($isStaffViewer ? 'rekap' : 'mine'));
        if (!in_array($mode, ['input', 'mine', 'rekap'], true)) {
            $mode = $isKetertiban ? 'input' : ($isStaffViewer ? 'rekap' : 'mine');
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
            $namesForGender = $gender === 'putra' ? $putraNames : $putriNames;
            $query->whereHas('santri', function ($q) use ($namesForGender) {
                $q->whereIn('nama_lengkap', $namesForGender);
            });
        } elseif ($user->role === \App\Enum\Role::SANTRI) {
            abort_unless($santriId, 403);
            $query->where('santri_id', $santriId);
        } elseif ($isStaffViewer) {
            // pengurus/degur dapat melihat semua catatan
        } else {
            abort(403);
        }

        $search = trim((string) request()->get('search', ''));
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                    ->orWhereHas('santri', fn ($sq) => $sq->where('nama_lengkap', 'like', "%{$search}%"));
            });
        }

        if ($isStaffViewer) {
            $genderFilter = request()->get('gender_filter', 'all');
            if (in_array($genderFilter, ['putra', 'putri'], true)) {
                $namesForGender = $genderFilter === 'putra' ? $putraNames : $putriNames;
                $query->whereHas('santri', function ($q) use ($namesForGender) {
                    $q->whereIn('nama_lengkap', $namesForGender);
                });
            }

            $kategoriFilter = request()->get('kategori_filter');
            if ($kategoriFilter) {
                $query->whereHas('kegiatan', fn ($kq) => $kq->where('kategori', $kategoriFilter));
            }

            $tanggalFilter = request()->get('tanggal');
            if ($tanggalFilter) {
                $query->whereDate('created_at', $tanggalFilter);
            }
        }

        $presensis = $query->paginate(20)->withQueryString();
        $santriList = collect();
        if ($isKetertiban) {
            $base = Santri::orderBy('nama_lengkap')->get(['id', 'nama_lengkap', 'tim']);
            $santriList = $base->filter(function ($s) use ($gender, $putraNames, $putriNames) {
                if ($gender === 'putra') {
                    return in_array($s->nama_lengkap, $putraNames, true);
                }
                return in_array($s->nama_lengkap, $putriNames, true);
            });
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
            'isStaffViewer' => $isStaffViewer,
            'genderFilter' => $isStaffViewer ? request()->get('gender_filter', 'all') : null,
            'kategoriFilter' => $isStaffViewer ? request()->get('kategori_filter') : null,
            'tanggalFilter' => $isStaffViewer ? request()->get('tanggal') : null,
        ]);
    }

    public function store(StorePresensiRequest $request): RedirectResponse
    {
        $this->ensureSantriRole();
        $this->authorize('create', Presensi::class);

        $data = $request->validated();

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

                Presensi::create([
                    'santri_id' => $santri->id,
                    'nama' => $santri->nama_lengkap,
                    'status' => $status,
                    'kegiatan_id' => $kegiatan->id,
                    'catatan' => $data['catatan'] ?? null,
                    'waktu' => $data['waktu'],
                ]);
            }

            return back()->with('success', 'Presensi batch berhasil disimpan');
        }

        // Single mode (fallback)
        $santri = Santri::findOrFail($data['santri_id']);

        $kegiatan = Kegiatan::firstOrCreate(
            ['kategori' => $data['kategori'], 'waktu' => $data['waktu']],
            ['catatan' => null]
        );

        Presensi::create([
            'santri_id' => $santri->id,
            'nama' => $data['nama'] ?? $santri->nama_lengkap,
            'status' => $data['status'],
            'kegiatan_id' => $kegiatan->id,
            'catatan' => $data['catatan'] ?? null,
            'waktu' => $data['waktu'],
        ]);

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
