<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\RecordVacationRequest;
use App\Http\Resources\VacationResource;
use App\Models\Occupation;
use App\Models\Vacation;
use App\Services\VacationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class VacationController extends Controller
{
    public function __construct(private readonly VacationService $service) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Vacation::class);

        $vacations = Vacation::visibleTo($request->user())
            ->orderByDesc('vacation_date')
            ->paginate(min((int) $request->integer('per_page', 15), 100));

        return VacationResource::collection($vacations);
    }

    public function store(RecordVacationRequest $request): JsonResponse
    {
        $this->authorize('create', Vacation::class);

        $data = $request->validated();
        $occupation = Occupation::findOrFail($data['occupation_id']);

        $vacation = $this->service->record(
            $occupation,
            $data['vacation_date'],
            $data['reason'] ?? null,
            $data['legal_basis'] ?? null,
            $data['notes'] ?? null,
        );

        return VacationResource::make($vacation)->response()->setStatusCode(201);
    }

    public function show(Request $request, int $vacation): VacationResource
    {
        $model = Vacation::visibleTo($request->user())->findOrFail($vacation);

        $this->authorize('view', $model);

        return VacationResource::make($model);
    }
}
