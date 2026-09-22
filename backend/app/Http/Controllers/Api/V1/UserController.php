<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Filters\UserFilter;
use App\Http\Requests\Api\V1\StoreUserRequest;
use App\Http\Requests\Api\V1\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class UserController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', User::class);

        $query = User::visibleTo($request->user())->with(['department', 'establishment', 'roles']);

        $users = (new UserFilter($request))->apply($query)
            ->orderBy('name')
            ->paginate(min((int) $request->integer('per_page', 15), 100));

        return UserResource::collection($users);
    }

    public function store(StoreUserRequest $request): JsonResponse
    {
        $this->authorize('create', User::class);

        $data = $request->validated();
        $role = $data['role'];
        unset($data['role']);

        $user = User::create($data);
        $user->assignRole($role);

        return UserResource::make($user->fresh(['department', 'establishment', 'roles']))
            ->response()
            ->setStatusCode(201);
    }

    public function update(UpdateUserRequest $request, int $user): UserResource
    {
        $model = User::visibleTo($request->user())->findOrFail($user);

        $this->authorize('update', $model);

        $data = $request->validated();
        $role = $data['role'];
        unset($data['role']);

        if (empty($data['password'])) {
            unset($data['password']);
        }

        $model->update($data);
        $model->syncRoles([$role]);

        return UserResource::make($model->fresh(['department', 'establishment', 'roles']));
    }

    public function destroy(Request $request, int $user): JsonResponse
    {
        $model = User::visibleTo($request->user())->findOrFail($user);

        $this->authorize('delete', $model);

        return $this->destroyOrConflict($model);
    }
}
