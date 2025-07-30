<?php

namespace App\Http\Controllers\Api;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Routing\Controller as BaseController;
use App\Models\HealthCareUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Models\Client;

class ClientController extends BaseController
{
    public function getConfiguration(Request $request, $id)
    {
        return Client::select([
            "id",
            "name",
            "splash_screen as field_header_image",
            "logo_path as field_logo",
            "phone_number as field_phone_number",
            "receptionist_contact as field_receptionist_contact",
            "primary_color",
            "secondary_color",
            "accent_color",
            "force_reschedule"
        ])->find($id);
    }
}
