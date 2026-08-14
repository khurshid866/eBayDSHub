<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    protected function isCompanyAuthorized(User $user, User $model): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        return !empty($user->company_id) && (int)$model->company_id === (int)$user->company_id;
    }

    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    public function view(User $user, User $model): bool
    {
        return $user->isAdmin() && $this->isCompanyAuthorized($user, $model);
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, User $model): bool
    {
        return $user->isAdmin() && $this->isCompanyAuthorized($user, $model);
    }

    public function delete(User $user, User $model): bool
    {
        return $user->isAdmin() && $user->id !== $model->id && $this->isCompanyAuthorized($user, $model);
    }
}
