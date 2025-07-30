<?php

namespace App\Policies;

use Chiiya\FilamentAccessControl\Models\FilamentUser as User;
use Illuminate\Database\Eloquent\Model;
use App\Models\Site;

class BasePolicy
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
        if ($user->hasPermissionTo('manage-sites')) {
            return true;
        }
        return false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Model $model): bool
    {
        if ($user->hasPermissionTo('manage-sites')) {
            if ($user->id == $model->created_by) {
                return true;
            }
        }
        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        if (request()->route()->getName() != "livewire.update")
            if (request()->route()->getAction("prefix") != "admin/sites")
                if (!session('active_site_id', false) && request()->route()->getName() != "filament.admin.resources.sites.index") {
                    return false;
                }
        if ($user->hasPermissionTo('manage-sites')) {
            if ($user->hasRole("single-site") && request()->route()->getAction("prefix") == "admin/sites") {

                $count = Site::where("created_by", $user->id)->count();
                if ($count > 0) {
                    return false;
                }
            }

            return true;
        }
        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Model $model): bool
    {
        if ($user->hasPermissionTo('manage-sites')) {
            if (get_class($model) == "App\Models\SitePage") {
                if ($model->site_id == session("active_site_id", null)) {
                    return true;
                }
            }
            if ($user->id == $model->created_by) {
                return true;
            }
        }
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Model $model): bool
    {

        if ($user->hasPermissionTo('manage-sites')) {
            if (get_class($model) == "App\Models\SitePage") {
                if ($model->site_id == session("active_site_id", null)) {
                    return true;
                }
            }
            if ($user->id == $model->created_by) {
                return true;
            }
        }
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Model $model): bool
    {
        if ($user->hasPermissionTo('manage-sites')) {
            if ($user->id == $model->created_by) {
                return true;
            }
        }
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Model $model): bool
    {
        if ($user->hasPermissionTo('manage-sites')) {
            if ($user->id == $model->created_by) {
                return true;
            }
        }
        return false;
    }
}
