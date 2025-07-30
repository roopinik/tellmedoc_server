<?php

namespace App\Policies;

use Chiiya\FilamentAccessControl\Models\FilamentUser as User;
use Illuminate\Database\Eloquent\Model;
use App\Models\Site;

class PrescriptionPolicy
{
    public function before(User $user, string $ability): bool|null
    {
        return false;
    }
    public function viewAny(User $user): bool
    {
        if ($user->hasPermissionTo('prescriptions.view')) {
            return true;
        }
        return false;
    }

    public function view(User $user, Model $model): bool
    {
        if ($user->hasPermissionTo('prescriptions.view')) {
            if ($user->client_id == $model->client_id) {
                return true;
            }
        }
        return false;
    }

    public function create(User $user){
        if ($user->hasPermissionTo('prescription.create')) {
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
        if ($user->hasPermissionTo('prescription.delete')) {
            if ($user->client_id == $model->client_id) {
                return true;
            }
        }
        return false;
    }
}
