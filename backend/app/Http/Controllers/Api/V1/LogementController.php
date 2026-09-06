<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Filters\LogementFilter;
use App\Http\Requests\Api\V1\StoreLogementRequest;
use App\Http\Requests\Api\V1\UpdateLogementRequest;
use App\Http\Resources\LogementResource;
use App\Models\Logement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class LogementController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Logement::class);

        $query = Logement::visibleTo($request->user())->with('establishment');

        $logements = (new LogementFilter($request))->apply($query)
            ->orderBy('inventory_number')
            ->paginate(min((int) $request->integer('per_page', 15), 100));

        return LogementResource::collection($logements);
    }

    public function store(StoreLogementRequest $request): JsonResponse
    {
        $this->authorize('create', Logement::class);

        $logement = Logement::create($request->validated());

        return LogementResource::make($logement->fresh('establishment'))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Request $request, int $logement): LogementResource
    {
        $model = Logement::visibleTo($request->user())
            ->with('establishment')
            ->findOrFail($logement);

        $this->authorize('view', $model);

        return LogementResource::make($model);
    }

    public function update(UpdateLogementRequest $request, int $logement): LogementResource
    {
        $model = Logement::visibleTo($request->user())->findOrFail($logement);

        $this->authorize('update', $model);

        $model->update($request->validated());

        return LogementResource::make($model->fresh('establishment'));
    }

    public function destroy(Request $request, int $logement): JsonResponse
    {
        $model = Logement::visibleTo($request->user())->findOrFail($logement);

        $this->authorize('delete', $model);

        return $this->destroyOrConflict($model);
    }
}
