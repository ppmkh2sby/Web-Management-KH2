<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProgressKeilmuanRequest;
use App\Http\Requests\UpdateProgressKeilmuanRequest;
use App\Models\ProgressKeilmuan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProgressKeilmuanController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', ProgressKeilmuan::class);

        $query = ProgressKeilmuan::query()
            ->with('santri.user')
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

        $progress = ProgressKeilmuan::create($request->validated());

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

        $progressKeilmuan->update($request->validated());

        return response()->json($progressKeilmuan->load('santri.user'));
    }

    public function destroy(ProgressKeilmuan $progressKeilmuan): JsonResponse
    {
        $this->authorize('delete', $progressKeilmuan);
        $progressKeilmuan->delete();

        return response()->json(['message' => 'Progress berhasil dihapus']);
    }
}
