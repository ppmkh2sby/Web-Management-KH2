<?php

namespace App\Http\Controllers\Santri;

use App\Enum\Role;
use App\Http\Controllers\Controller;
use App\Models\LogKeluarMasuk;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LogKeluarMasukController extends Controller
{
    private function ensureAllowedRole(): void
    {
        abort_unless(auth()->check(), 403);

        $allowed = [Role::SANTRI, Role::PENGURUS, Role::DEWAN_GURU, Role::WALI];
        abort_unless(in_array(auth()->user()->role, $allowed, true), 403);
    }

    private function ensureSantriRole(): void
    {
        abort_unless(auth()->check() && auth()->user()->role === Role::SANTRI, 403);
    }

    private function splitRentang(?string $rentang): array
    {
        $parts = preg_split('/\s*-\s*/', (string) $rentang, 2) ?: [];

        return [
            'waktu_keluar' => trim((string) ($parts[0] ?? '')),
            'waktu_masuk' => trim((string) ($parts[1] ?? '')),
        ];
    }

    private function attachWaktuFields(LogKeluarMasuk $log): LogKeluarMasuk
    {
        $times = $this->splitRentang($log->rentang);
        $log->setAttribute('waktu_keluar', $times['waktu_keluar']);
        $log->setAttribute('waktu_masuk', $times['waktu_masuk']);

        return $log;
    }

    public function index(Request $request): View|RedirectResponse
    {
        $this->ensureAllowedRole();
        $this->authorize('viewAny', LogKeluarMasuk::class);

        $user = auth()->user();

        if ($user->role === Role::WALI) {
            $firstChildCode = $user->waliOf()
                ->orderBy('santris.nama_lengkap')
                ->value('santris.code');

            if (filled($firstChildCode)) {
                return redirect()->route('wali.anak.log', ['santriCode' => $firstChildCode]);
            }

            return redirect()->route('profile.edit')->with('status', 'Akun wali belum terhubung ke data anak.');
        }

        $isStaffViewer = in_array($user->role, [Role::PENGURUS, Role::DEWAN_GURU], true);

        if ($isStaffViewer) {
            $genderFilter = (string) $request->get('gender_filter', 'all');
            if (! in_array($genderFilter, ['all', 'putra', 'putri'], true)) {
                $genderFilter = 'all';
            }

            $search = trim((string) $request->get('search', ''));

            $query = LogKeluarMasuk::query()
                ->with(['santri:id,nama_lengkap,gender,tim,code'])
                ->latest('tanggal_pengajuan')
                ->latest('id');

            if (in_array($genderFilter, ['putra', 'putri'], true)) {
                $query->whereHas('santri', function ($q) use ($genderFilter) {
                    $q->where('gender', $genderFilter);
                });
            }

            if ($search !== '') {
                $query->where(function ($q) use ($search) {
                    $q->where('jenis', 'like', "%{$search}%")
                        ->orWhere('catatan', 'like', "%{$search}%")
                        ->orWhereHas('santri', fn ($sq) => $sq->where('nama_lengkap', 'like', "%{$search}%"));
                });
            }

            $logs = $query
                ->paginate(12)
                ->withQueryString()
                ->through(fn (LogKeluarMasuk $log) => $this->attachWaktuFields($log));

            return view('santri.pages.data.log', [
                'santri' => null,
                'logs' => $logs,
                'mode' => 'team',
                'isStaffViewer' => true,
                'genderFilter' => $genderFilter,
                'search' => $search,
            ]);
        }

        $this->ensureSantriRole();
        $santri = $user->santri;
        abort_unless($santri, 403);

        $mode = (string) $request->get('mode', 'input');
        if (! in_array($mode, ['input', 'mine'], true)) {
            $mode = 'input';
        }

        $logs = LogKeluarMasuk::where('santri_id', $santri->id)
            ->latest('tanggal_pengajuan')
            ->latest('id')
            ->get()
            ->map(fn (LogKeluarMasuk $log) => $this->attachWaktuFields($log));

        return view('santri.pages.data.log', [
            'santri' => $santri,
            'logs' => $logs,
            'mode' => $mode,
            'isStaffViewer' => false,
            'genderFilter' => 'all',
            'search' => '',
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->ensureSantriRole();
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
            'status' => 'tercatat',
            'catatan' => $data['catatan'] ?? null,
        ]);

        return redirect()
            ->route('santri.data.log', ['mode' => 'mine'])
            ->with('success', 'Log keluar/masuk berhasil dicatat.');
    }

    public function update(Request $request, LogKeluarMasuk $logKeluarMasuk): RedirectResponse
    {
        $this->ensureSantriRole();
        $this->authorize('update', $logKeluarMasuk);

        $data = $request->validate([
            'tanggal' => ['required', 'date'],
            'tujuan' => ['required', 'string', 'max:150'],
            'waktu_keluar' => ['required', 'date_format:H:i'],
            'waktu_masuk' => ['required', 'date_format:H:i'],
            'catatan' => ['nullable', 'string'],
        ]);

        $rentang = $data['waktu_keluar'] . ' - ' . $data['waktu_masuk'];

        $logKeluarMasuk->update([
            'tanggal_pengajuan' => $data['tanggal'],
            'jenis' => $data['tujuan'],
            'rentang' => $rentang,
            'status' => 'tercatat',
            'catatan' => $data['catatan'] ?? null,
        ]);

        return redirect()
            ->route('santri.data.log', ['mode' => 'mine'])
            ->with('success', 'Log keluar/masuk berhasil diperbarui.');
    }

    public function destroy(LogKeluarMasuk $logKeluarMasuk): RedirectResponse
    {
        $this->ensureSantriRole();
        $this->authorize('delete', $logKeluarMasuk);

        $logKeluarMasuk->delete();

        return redirect()
            ->route('santri.data.log', ['mode' => 'mine'])
            ->with('success', 'Log keluar/masuk dihapus.');
    }
}
