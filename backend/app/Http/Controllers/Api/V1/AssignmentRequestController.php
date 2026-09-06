<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\AcceptAssignmentRequestRequest;
use App\Http\Requests\Api\V1\RejectAssignmentRequestRequest;
use App\Http\Requests\Api\V1\StoreAssignmentRequestRequest;
use App\Http\Resources\AssignmentRequestResource;
use App\Http\Resources\OccupationResource;
use App\Models\AssignmentRequest;
use App\Services\AssignmentRequestService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class AssignmentRequestController extends Controller
{
    public function __construct(private readonly AssignmentRequestService $service)
    {
    }

    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', AssignmentRequest::class);

        $query = AssignmentRequest::visibleTo($request->user())->with(['logement', 'occupant']);

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        $requests = $query->orderByDesc('submitted_at')
            ->paginate(min((int) $request->integer('per_page', 15), 100));

        return AssignmentRequestResource::collection($requests);
    }

    public function store(StoreAssignmentRequestRequest $request): JsonResponse
    {
        $this->authorize('create', AssignmentRequest::class);

        $assignmentRequest = AssignmentRequest::create([
            ...$request->validated(),
            'submitted_at' => now()->toDateString(),
        ]);

        return AssignmentRequestResource::make($assignmentRequest->fresh(['logement', 'occupant']))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Request $request, int $assignmentRequest): AssignmentRequestResource
    {
        $model = AssignmentRequest::visibleTo($request->user())
            ->with(['logement', 'occupant'])
            ->findOrFail($assignmentRequest);

        $this->authorize('view', $model);

        return AssignmentRequestResource::make($model);
    }

    public function accept(AcceptAssignmentRequestRequest $request, int $assignmentRequest): JsonResponse
    {
        $this->authorize('decide', AssignmentRequest::class);

        $model = AssignmentRequest::visibleTo($request->user())->findOrFail($assignmentRequest);

        $result = $this->service->accept($model, $request->user(), $request->validated());

        return response()->json([
            'data' => new AssignmentRequestResource($result['request']->fresh(['logement', 'occupant'])),
            'occupation' => new OccupationResource($result['occupation']),
            'rival_requests' => AssignmentRequestResource::collection($result['rival_requests']),
        ]);
    }

    public function reject(RejectAssignmentRequestRequest $request, int $assignmentRequest): AssignmentRequestResource
    {
        $this->authorize('decide', AssignmentRequest::class);

        $model = AssignmentRequest::visibleTo($request->user())->findOrFail($assignmentRequest);

        $updated = $this->service->reject($model, $request->user(), $request->validated()['notes'] ?? null);

        return AssignmentRequestResource::make($updated->fresh(['logement', 'occupant']));
    }

    public function reset(Request $request, int $assignmentRequest): AssignmentRequestResource
    {
        $this->authorize('decide', AssignmentRequest::class);

        $model = AssignmentRequest::visibleTo($request->user())->findOrFail($assignmentRequest);

        $updated = $this->service->reset($model);

        return AssignmentRequestResource::make($updated->fresh(['logement', 'occupant']));
    }
}
