<?php

namespace App\Policies;

use Chiiya\FilamentAccessControl\Models\FilamentUser as User;
use Illuminate\Database\Eloquent\Model;
use App\Models\Site;

class SharedDocumentPolicy
{
    public function before(User $user, string $ability): bool|null
    {
        return null;
    }
    public function viewAny(User $user): bool
    {
        if ($user->hasPermissionTo('shareddocument.view')) {
            return true;
        }
        return false;
    }

    public function view(User $user, Model $model): bool
    {
        if ($user->hasPermissionTo('shareddocument.view')) {
            return true;
        }
        return false;
    }

    public function create(User $user){
        if ($user->hasPermissionTo('shareddocument.create')) {
            return true;
        }
        return false;
    }

    public function update(User $user, Model $model): bool
    {
        return false;
    }

    public function delete(User $user, Model $model): bool
    {
        if ($user->hasPermissionTo('shareddocument.delete')) {
            if ($user->client_id == $model->client_id) {
                return true;
            }
        }
        return false;
    }
}
