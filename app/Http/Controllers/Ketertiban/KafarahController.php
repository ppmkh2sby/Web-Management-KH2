<?php

namespace App\Http\Controllers\Ketertiban;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreKafarahRequest;
use App\Http\Requests\UpdateKafarahRequest;
use App\Models\Kafarah;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class KafarahController extends Controller
{
    private function ensureKetertiban(Request $request): void
    {
        abort_unless(
            $request->user()?->isKetertiban(),
            403,
            'Hanya tim ketertiban yang dapat mengelola kafarah.'
        );
    }

    public function store(StoreKafarahRequest $request): RedirectResponse
    {
        $this->ensureKetertiban($request);
        $this->authorize('create', Kafarah::class);

        Kafarah::create($request->validated());

        return back()->with('success', 'Kafarah berhasil ditambahkan.');
    }

    public function update(UpdateKafarahRequest $request, Kafarah $kafarah): RedirectResponse
    {
        $this->ensureKetertiban($request);
        $this->authorize('update', $kafarah);

        $kafarah->update($request->validated());

        return back()->with('success', 'Kafarah berhasil diperbarui.');
    }

    public function destroy(Request $request, Kafarah $kafarah): RedirectResponse
    {
        $this->ensureKetertiban($request);
        $this->authorize('delete', $kafarah);

        $kafarah->delete();

        return back()->with('success', 'Kafarah berhasil dihapus.');
    }
}
