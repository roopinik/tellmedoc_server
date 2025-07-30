<?php

namespace App\Policies;

use Chiiya\FilamentAccessControl\Models\FilamentUser as User;
use Illuminate\Database\Eloquent\Model;
use App\Models\Site;

class HealthCareUserPolicy
{
    /**
     * Create a new policy instance.
     */
    public function __construct()
    {
        //
    }

    public function before(User $user, string $ability): bool|null
    {
        if ($user->hasRole('super-admin')) {
            return true;
        }
        return null;
    }

    public function viewAny(User $user): bool
    {
        return false;
    }

    public function view(User $user, Model $model): bool
    {
        return false;
    }

    public function create(User $user)
    {
        if ($user->hasRole('healthcare.admin')) {
            return true;
        }
        return false;
    }

    public function update(User $user, Model $model): bool
    {
        if ($user->hasRole('healthcare.admin')) {
            if ($user->client_id != $model->client_id)
                return false;
            return true;
        }
        if ($user->hasRole('doctor')) {
            if ($user->id == $model->id)
                return true;
        }
        return false;
    }

    public function delete(User $user, Model $model): bool
    {
        return false;
    }
}
