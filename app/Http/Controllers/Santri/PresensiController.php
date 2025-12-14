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
    private function ensureSantriRole(): void
    {
        abort_unless(auth()->check() && auth()->user()->role === \App\Enum\Role::SANTRI, 403);
    }

    public function index(): View
    {
        $this->ensureSantriRole();
        $this->authorize('viewAny', Presensi::class);

        $user = auth()->user();
        $isKetertiban = $user->isKetertiban();
        $santriId = $user->santri?->id;

        $mode = request()->get('mode', $isKetertiban ? 'input' : 'mine');
        if (!in_array($mode, ['input', 'mine'], true)) {
            $mode = $isKetertiban ? 'input' : 'mine';
        }

        $query = Presensi::with(['santri', 'kegiatan'])->latest('updated_at');

        if (! $isKetertiban || $mode === 'mine') {
            abort_unless($santriId, 403);
            $query->where('santri_id', $santriId);
        }

        $presensis = $query->paginate(20);
        $santriList = $isKetertiban
            ? Santri::orderBy('nama_lengkap')->get(['id', 'nama_lengkap', 'tim'])
            : collect();

        return view('santri.presensi.index', [
            'presensis' => $presensis,
            'santriList' => $santriList,
            'canManage' => $isKetertiban,
            'canEdit' => $isKetertiban && $mode === 'input',
            'mode' => $mode,
            'statuses' => Presensi::STATUS,
            'kategoriOptions' => Kegiatan::KATEGORI,
            'waktuOptions' => Presensi::WAKTU,
        ]);
    }

    public function store(StorePresensiRequest $request): RedirectResponse
    {
        $this->ensureSantriRole();
        $this->authorize('create', Presensi::class);

        $data = $request->validated();
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
