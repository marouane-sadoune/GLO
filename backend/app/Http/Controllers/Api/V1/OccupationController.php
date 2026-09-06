<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\OccupationEndReason;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\EndOccupationRequest;
use App\Http\Resources\OccupationResource;
use App\Models\Occupation;
use App\Services\OccupationService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class OccupationController extends Controller
{
    public function __construct(private readonly OccupationService $service) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Occupation::class);

        $query = Occupation::visibleTo($request->user())->with(['logement', 'occupant']);

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        $occupations = $query->orderByDesc('start_date')
            ->paginate(min((int) $request->integer('per_page', 15), 100));

        return OccupationResource::collection($occupations);
    }

    public function show(Request $request, int $occupation): OccupationResource
    {
        $model = Occupation::visibleTo($request->user())
            ->with(['logement', 'occupant'])
            ->findOrFail($occupation);

        $this->authorize('view', $model);

        return OccupationResource::make($model);
    }

    public function end(EndOccupationRequest $request, int $occupation): OccupationResource
    {
        $model = Occupation::visibleTo($request->user())->findOrFail($occupation);

        $this->authorize('manage', $model);

        $data = $request->validated();

        $updated = $this->service->end(
            $model,
            OccupationEndReason::from($data['end_reason']),
            $data['end_date'],
            $data['notes'] ?? null,
        );

        return OccupationResource::make($updated->fresh(['logement', 'occupant']));
    }
}
