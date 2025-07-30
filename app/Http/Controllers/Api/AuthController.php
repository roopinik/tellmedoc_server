<?php

namespace App\Http\Controllers\Api;

use App\Services\TDAuthService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Routing\Controller as BaseController;
use App\Models\HealthCareUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Auth;

class AuthController extends BaseController
{
    use AuthorizesRequests;
    public $authService;
    public function __construct()
    {
        $this->authService = new TDAuthService();
    }

    public function checkMobile(Request $request)
    {
        $data = $request->validate(["mobile" => "required|numeric", "client_id" => "required"]);
        $mobile = $data["mobile"];
        $clientId = $data["client_id"];
        $user = HealthCareUser::where("mobile_number", "like", "%$mobile")->first();
        $uuid = Str::uuid();

        try {
            if ($user == null) {
                $user = new HealthCareUser();
                $user->uuid = $uuid;
                $user->first_name = $uuid;
                $user->last_name = $uuid;
                $user->password = Hash::make(Str::random(40));
                $user->client_id = $clientId;
                $user->email = $uuid . "@telmedoc.com";
                $user->mobile_number = $mobile;
            }
            $user->otp_expires_at = Carbon::createFromTimestamp(time() + 300);
            $user->otp = rand(10000, 99999);
            $user->save();
            $user->assignRole('subscriber');
            return ["status" => 200, "data" => null, "message" => "otp sent to your mobile number."];
        } catch (\Throwable $e) {
            return response()->json(["status" => 500, "data" => null, "message" => $e->getMessage()], 500);
        }
    }

    public function verifyOtp(Request $request)
    {
        $data = $request->validate(["mobile" => "required|numeric", "otp" => "required|numeric"]);
        $mobile = $data["mobile"];
        $otp = $data["otp"];
        $user = HealthCareUser::where("mobile_number", "like", "%$mobile")
            // ->where("otp", $otp)
            // ->where("otp_expires_at", ">=", Carbon::now())
            ->first();
        if ($user == null) {
            return response()->json(null, 403);
        }
        $token = $user->createToken("subscriber_token", ["patient-app"]);
        return ['token' => $token->plainTextToken];
    }

    public function generateToken(Request $request)
    {
        $data = $request->validate(["email" => "required|email", "password" => "required|min:6"]);
        $email = $data["email"];
        $password = $data["password"];

        $user = HealthCareUser::where("email", "$email")
            ->first();
        if (!Hash::check($password, $user->password)) {
            abort(401, "authentication failed");
        }
        $token = $user->createToken("client_token", ["client-app"]);
        $role = $this->authService->getRole($user);
        if ($role == null) {
            abort(401, "authentication failed");
        }
        return ['token' => $token->plainTextToken, "role" => $role, "client_id" => $user->client_id];
    }

    public function getUserProfile()
    {
        $user = auth()->user();
        return [
            "id" => $user->id,
            "email" => $user->email,
            "mobile_number" => $user->mobile_number,
            "profile_pic" => $user->profile_pic,
            "first_name" => $user->first_name,
            "last_name" => $user->last_name,
            "subscription_plan" => $user->client->subscription_plan,
            "collect_patient_id" => $user->client->collect_patient_id,
            "force_reschedule" => $user->client->force_reschedule,
            "health_care_type" => $user->client->health_care_type,
        ];
    }
}
