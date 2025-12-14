<?php

namespace App\Http\Controllers\Ketertiban;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreKehadiranRequest;
use App\Http\Requests\UpdateKehadiranRequest;
use App\Models\Kehadiran;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class KehadiranController extends Controller
{
    private function ensureKetertiban(Request $request): void
    {
        abort_unless(
            $request->user()?->isKetertiban(),
            403,
            'Hanya tim ketertiban yang dapat mengelola presensi.'
        );
    }

    public function store(StoreKehadiranRequest $request): RedirectResponse
    {
        $this->ensureKetertiban($request);
        $this->authorize('create', Kehadiran::class);

        Kehadiran::create($request->validated());

        return back()->with('success', 'Presensi berhasil ditambahkan.');
    }

    public function update(UpdateKehadiranRequest $request, Kehadiran $kehadiran): RedirectResponse
    {
        $this->ensureKetertiban($request);
        $this->authorize('update', $kehadiran);

        $kehadiran->update($request->validated());

        return back()->with('success', 'Presensi berhasil diperbarui.');
    }

    public function destroy(Request $request, Kehadiran $kehadiran): RedirectResponse
    {
        $this->ensureKetertiban($request);
        $this->authorize('delete', $kehadiran);

        $kehadiran->delete();

        return back()->with('success', 'Presensi berhasil dihapus.');
    }
}
