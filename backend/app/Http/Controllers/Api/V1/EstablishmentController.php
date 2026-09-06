<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Filters\EstablishmentFilter;
use App\Http\Requests\Api\V1\StoreEstablishmentRequest;
use App\Http\Requests\Api\V1\UpdateEstablishmentRequest;
use App\Http\Resources\EstablishmentResource;
use App\Models\Establishment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class EstablishmentController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Establishment::class);

        $query = Establishment::visibleTo($request->user())
            ->with('department')
            ->withCount('logements');

        $establishments = (new EstablishmentFilter($request))->apply($query)
            ->orderBy('name_fr')
            ->paginate(min((int) $request->integer('per_page', 15), 100));

        return EstablishmentResource::collection($establishments);
    }

    public function store(StoreEstablishmentRequest $request): JsonResponse
    {
        $this->authorize('create', Establishment::class);

        $establishment = Establishment::create($request->validated());

        return EstablishmentResource::make($establishment->fresh('department'))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Request $request, int $establishment): EstablishmentResource
    {
        $model = Establishment::visibleTo($request->user())
            ->with('department')
            ->withCount('logements')
            ->findOrFail($establishment);

        $this->authorize('view', $model);

        return EstablishmentResource::make($model);
    }

    public function update(UpdateEstablishmentRequest $request, int $establishment): EstablishmentResource
    {
        $model = Establishment::visibleTo($request->user())->findOrFail($establishment);

        $this->authorize('update', $model);

        $model->update($request->validated());

        return EstablishmentResource::make($model->fresh('department'));
    }

    public function destroy(Request $request, int $establishment): JsonResponse
    {
        $model = Establishment::visibleTo($request->user())->findOrFail($establishment);

        $this->authorize('delete', $model);

        return $this->destroyOrConflict($model);
    }
}
