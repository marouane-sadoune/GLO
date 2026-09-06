<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreDepartmentRequest;
use App\Http\Requests\Api\V1\UpdateDepartmentRequest;
use App\Http\Resources\DepartmentResource;
use App\Models\Department;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class DepartmentController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Department::class);

        $departments = Department::visibleTo($request->user())
            ->withCount('establishments')
            ->orderBy('name_fr')
            ->paginate(min((int) $request->integer('per_page', 15), 100));

        return DepartmentResource::collection($departments);
    }

    public function store(StoreDepartmentRequest $request): JsonResponse
    {
        $this->authorize('create', Department::class);

        $department = Department::create($request->validated());

        return DepartmentResource::make($department)->response()->setStatusCode(201);
    }

    public function show(Request $request, int $department): DepartmentResource
    {
        $model = Department::visibleTo($request->user())
            ->withCount('establishments')
            ->findOrFail($department);

        $this->authorize('view', $model);

        return DepartmentResource::make($model);
    }

    public function update(UpdateDepartmentRequest $request, int $department): DepartmentResource
    {
        $model = Department::visibleTo($request->user())->findOrFail($department);

        $this->authorize('update', $model);

        $model->update($request->validated());

        return DepartmentResource::make($model);
    }

    public function destroy(Request $request, int $department): JsonResponse
    {
        $model = Department::visibleTo($request->user())->findOrFail($department);

        $this->authorize('delete', $model);

        return $this->destroyOrConflict($model);
    }
}
