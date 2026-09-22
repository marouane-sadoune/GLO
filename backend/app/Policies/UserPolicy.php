<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('users.view');
    }

    public function view(User $user, User $model): bool
    {
        return $user->can('users.view') && $this->inScope($user, $model);
    }

    public function create(User $user): bool
    {
        return $user->can('users.manage');
    }

    public function update(User $user, User $model): bool
    {
        return $user->can('users.manage') && $this->inScope($user, $model);
    }

    public function delete(User $user, User $model): bool
    {
        return $user->can('users.manage') && $user->isNot($model) && $this->inScope($user, $model);
    }

    private function inScope(User $user, User $model): bool
    {
        return User::visibleTo($user)->whereKey($model->id)->exists();
    }
}
