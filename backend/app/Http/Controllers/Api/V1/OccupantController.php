<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Filters\OccupantFilter;
use App\Http\Requests\Api\V1\StoreOccupantRequest;
use App\Http\Requests\Api\V1\UpdateOccupantRequest;
use App\Http\Resources\OccupantResource;
use App\Models\Occupant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class OccupantController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Occupant::class);

        $query = Occupant::visibleTo($request->user())->with('establishment');

        $occupants = (new OccupantFilter($request))->apply($query)
            ->orderBy('last_name_fr')
            ->paginate(min((int) $request->integer('per_page', 15), 100));

        return OccupantResource::collection($occupants);
    }

    public function store(StoreOccupantRequest $request): JsonResponse
    {
        $this->authorize('create', Occupant::class);

        $occupant = Occupant::create($request->validated());

        return OccupantResource::make($occupant->fresh('establishment'))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Request $request, int $occupant): OccupantResource
    {
        $model = Occupant::visibleTo($request->user())
            ->with('establishment')
            ->findOrFail($occupant);

        $this->authorize('view', $model);

        return OccupantResource::make($model);
    }

    public function update(UpdateOccupantRequest $request, int $occupant): OccupantResource
    {
        $model = Occupant::visibleTo($request->user())->findOrFail($occupant);

        $this->authorize('update', $model);

        $model->update($request->validated());

        return OccupantResource::make($model->fresh('establishment'));
    }

    public function destroy(Request $request, int $occupant): JsonResponse
    {
        $model = Occupant::visibleTo($request->user())->findOrFail($occupant);

        $this->authorize('delete', $model);

        return $this->destroyOrConflict($model);
    }
}
