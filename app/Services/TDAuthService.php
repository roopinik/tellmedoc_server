<?php

namespace App\Services;

class TDAuthService
{
    public function getRole($user)
    {
        $roles = $user->getRoleNames();
        if ($roles->contains("receptionist"))
            return "receptionist";
        if ($roles->contains("doctor"))
            return "doctor";
        if ($roles->contains("technician"))
            return "technician";
        if ($roles->contains("healthcare.admin"))
            return "healthcare.admin";
        return null;
    }
}
