<?php

namespace App\Policies;

use Chiiya\FilamentAccessControl\Models\FilamentUser as User;
use Illuminate\Database\Eloquent\Model;
use App\Models\Site;

class PersonPolicy
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
        return false;
    }

    public function viewAny(User $user): bool
    {

        return false;
    }

}
