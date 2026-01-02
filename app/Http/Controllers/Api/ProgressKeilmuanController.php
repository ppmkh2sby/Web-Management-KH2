<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProgressKeilmuanRequest;
use App\Http\Requests\UpdateProgressKeilmuanRequest;
use App\Enum\Role;
use App\Models\ProgressKeilmuan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProgressKeilmuanController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', ProgressKeilmuan::class);

        $user = $request->user();
        $santriId = optional($user?->santri)->id;

        $query = ProgressKeilmuan::query()
            ->with('santri.user')
            ->when($user?->role === Role::SANTRI && $santriId, fn ($q) => $q->where('santri_id', $santriId))
            ->when($request->filled('santri_id'), fn ($q) => $q->where('santri_id', $request->integer('santri_id')))
            ->when($request->filled('search'), function ($q) use ($request) {
                $term = $request->input('search');
                $q->where('judul', 'like', "%{$term}%");
            });

        return response()->json($query->orderByDesc('updated_at')->paginate($request->integer('per_page', 15)));
    }

    public function store(StoreProgressKeilmuanRequest $request): JsonResponse
    {
        $this->authorize('create', ProgressKeilmuan::class);

        $data = $request->validated();

        if ($request->user()?->role === Role::SANTRI) {
            $santriId = optional($request->user()->santri)->id;
            abort_unless($santriId, 403, 'Data santri tidak ditemukan');
            $data['santri_id'] = $santriId;
        }

        $progress = ProgressKeilmuan::create($data);

        return response()->json($progress->load('santri.user'), 201);
    }

    public function show(ProgressKeilmuan $progressKeilmuan): JsonResponse
    {
        $this->authorize('view', $progressKeilmuan);

        return response()->json($progressKeilmuan->load('santri.user'));
    }

    public function update(UpdateProgressKeilmuanRequest $request, ProgressKeilmuan $progressKeilmuan): JsonResponse
    {
        $this->authorize('update', $progressKeilmuan);

        $data = $request->validated();

        if ($request->user()?->role === Role::SANTRI) {
            $santriId = optional($request->user()->santri)->id;
            abort_unless($santriId, 403, 'Data santri tidak ditemukan');
            $data['santri_id'] = $santriId;
        }

        $progressKeilmuan->update($data);

        return response()->json($progressKeilmuan->load('santri.user'));
    }

    public function destroy(ProgressKeilmuan $progressKeilmuan): JsonResponse
    {
        $this->authorize('delete', $progressKeilmuan);
        $progressKeilmuan->delete();

        return response()->json(['message' => 'Progress berhasil dihapus']);
    }
}
