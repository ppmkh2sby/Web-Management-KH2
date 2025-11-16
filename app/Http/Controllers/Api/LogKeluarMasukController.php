<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreLogKeluarMasukRequest;
use App\Http\Requests\UpdateLogKeluarMasukRequest;
use App\Models\LogKeluarMasuk;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LogKeluarMasukController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', LogKeluarMasuk::class);

        $query = LogKeluarMasuk::query()
            ->with('santri.user')
            ->when($request->filled('santri_id'), fn ($q) => $q->where('santri_id', $request->integer('santri_id')))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->input('status')));

        return response()->json($query->orderByDesc('tanggal_pengajuan')->paginate($request->integer('per_page', 15)));
    }

    public function store(StoreLogKeluarMasukRequest $request): JsonResponse
    {
        $this->authorize('create', LogKeluarMasuk::class);

        $log = LogKeluarMasuk::create($request->validated());

        return response()->json($log->load('santri.user'), 201);
    }

    public function show(LogKeluarMasuk $logKeluarMasuk): JsonResponse
    {
        $this->authorize('view', $logKeluarMasuk);

        return response()->json($logKeluarMasuk->load('santri.user'));
    }

    public function update(UpdateLogKeluarMasukRequest $request, LogKeluarMasuk $logKeluarMasuk): JsonResponse
    {
        $this->authorize('update', $logKeluarMasuk);

        $logKeluarMasuk->update($request->validated());

        return response()->json($logKeluarMasuk->load('santri.user'));
    }

    public function destroy(LogKeluarMasuk $logKeluarMasuk): JsonResponse
    {
        $this->authorize('delete', $logKeluarMasuk);
        $logKeluarMasuk->delete();

        return response()->json(['message' => 'Log izin berhasil dihapus']);
    }
}
