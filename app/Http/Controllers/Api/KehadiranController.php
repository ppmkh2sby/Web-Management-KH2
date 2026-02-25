<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Enum\Role;
use App\Http\Requests\StoreKehadiranRequest;
use App\Http\Requests\UpdateKehadiranRequest;
use App\Models\Kehadiran;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class KehadiranController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Kehadiran::class);

        $user = $request->user();
        $isStaff = $user && ($user->role === Role::ADMIN || in_array($user->role?->value, Role::staff(), true));
        $isKetertiban = $user?->isKetertiban() ?? false;

        $query = Kehadiran::query()
            ->with('santri.user')
            ->when($request->filled('santri_id'), fn ($q) => $q->where('santri_id', $request->integer('santri_id')))
            ->when($request->filled('status'), fn ($q) => $q->status($request->input('status')))
            ->when($request->boolean('only_mine'), function ($q) use ($request) {
                $santri = $request->user()?->santri;
                if ($santri) {
                    $q->where('santri_id', $santri->id);
                }
            });

        // Batasi akses data untuk non-staff & non-ketertiban
        if (!$isStaff && !$isKetertiban && $user) {
            if ($user->role === Role::SANTRI && $user->santri) {
                $query->where('santri_id', $user->santri->id);
            } elseif ($user->role === Role::WALI) {
                $santriIds = $user->waliOf()->pluck('santris.id');
                $query->whereIn('santri_id', $santriIds);
            }
        }

        return response()->json(
            $query->orderByDesc('tanggal')->paginate($request->integer('per_page', 15))
        );
    }

    public function store(StoreKehadiranRequest $request): JsonResponse
    {
        $this->authorize('create', Kehadiran::class);

        $kehadiran = Kehadiran::create($request->validated());

        return response()->json($kehadiran->load('santri.user'), 201);
    }

    public function show(Kehadiran $kehadiran): JsonResponse
    {
        $this->authorize('view', $kehadiran);

        return response()->json($kehadiran->load('santri.user'));
    }

    public function update(UpdateKehadiranRequest $request, Kehadiran $kehadiran): JsonResponse
    {
        $this->authorize('update', $kehadiran);

        $kehadiran->update($request->validated());

        return response()->json($kehadiran->load('santri.user'));
    }

    public function destroy(Kehadiran $kehadiran): JsonResponse
    {
        $this->authorize('delete', $kehadiran);
        $kehadiran->delete();

        return response()->json(['message' => 'Kehadiran berhasil dihapus']);
    }
}
