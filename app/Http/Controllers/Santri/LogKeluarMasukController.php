<?php

namespace App\Http\Controllers\Santri;

use App\Http\Controllers\Controller;
use App\Models\LogKeluarMasuk;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LogKeluarMasukController extends Controller
{
    private function ensureSantri(): void
    {
        abort_unless(auth()->check() && auth()->user()->role === \App\Enum\Role::SANTRI, 403);
    }

    public function index(Request $request): View
    {
        $this->ensureSantri();

        $santri = auth()->user()->santri;
        abort_unless($santri, 403);

        $mode = $request->get('mode', 'input');
        if (! in_array($mode, ['input', 'mine'], true)) {
            $mode = 'input';
        }

        $logs = LogKeluarMasuk::where('santri_id', $santri->id)
            ->latest('tanggal_pengajuan')
            ->get();

        return view('santri.pages.data.log', [
            'santri' => $santri,
            'logs' => $logs,
            'mode' => $mode,
            'statuses' => LogKeluarMasuk::STATUSES,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->ensureSantri();
        $santri = auth()->user()->santri;
        abort_unless($santri, 403);

        $data = $request->validate([
            'tanggal' => ['required', 'date'],
            'tujuan' => ['required', 'string', 'max:150'],
            'waktu_keluar' => ['required', 'date_format:H:i'],
            'waktu_masuk' => ['required', 'date_format:H:i'],
            'catatan' => ['nullable', 'string'],
        ]);

        $rentang = $data['waktu_keluar'] . ' - ' . $data['waktu_masuk'];

        $this->authorize('create', LogKeluarMasuk::class);

        LogKeluarMasuk::create([
            'santri_id' => $santri->id,
            'tanggal_pengajuan' => $data['tanggal'],
            'jenis' => $data['tujuan'],
            'rentang' => $rentang,
            'status' => 'proses',
            'catatan' => $data['catatan'] ?? null,
        ]);

        return back()->with('success', 'Pengajuan keluar/masuk berhasil dicatat.');
    }

    public function update(Request $request, LogKeluarMasuk $log): RedirectResponse
    {
        $this->ensureSantri();
        $this->authorize('update', $log);

        $data = $request->validate([
            'tanggal' => ['required', 'date'],
            'tujuan' => ['required', 'string', 'max:150'],
            'waktu_keluar' => ['required', 'date_format:H:i'],
            'waktu_masuk' => ['required', 'date_format:H:i'],
            'catatan' => ['nullable', 'string'],
        ]);

        $rentang = $data['waktu_keluar'] . ' - ' . $data['waktu_masuk'];

        $log->update([
            'tanggal_pengajuan' => $data['tanggal'],
            'jenis' => $data['tujuan'],
            'rentang' => $rentang,
            'catatan' => $data['catatan'] ?? null,
        ]);

        return back()->with('success', 'Pengajuan berhasil diperbarui.');
    }

    public function destroy(LogKeluarMasuk $log): RedirectResponse
    {
        $this->ensureSantri();
        $this->authorize('delete', $log);

        $log->delete();

        return back()->with('success', 'Pengajuan dihapus.');
    }
}
