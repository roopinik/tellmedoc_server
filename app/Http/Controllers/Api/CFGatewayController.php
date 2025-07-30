<?php

namespace App\Http\Controllers\Api;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Routing\Controller as BaseController;
use App\Models\HealthCareUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Models\Appointment;
use App\Models\CashFreePayment;
use App\Models\CashFreeSession;
use Illuminate\Support\Facades\Http;

class CFGatewayController extends BaseController
{
    public function createSession(Request $request)
    {

        $data = $request->validate(["id" => "required|numeric", "type" => "required"]);

        $pgUrl = env("CF_URL");
        $clientId = env("CF_CLIENT_ID");
        $secret = env("CF_CLIENT_SECRET");
        $mode = env("CF_MODE");
        $uuid = Str::uuid();
        $id = $data["id"];
        $type = $data["type"];
        $appointment = Appointment::find($id);
        $user = auth("sanctum")->user();
       
        $session = new CashFreeSession();

        $session->fill([
            "type" => $type,
            "uuid" => $uuid,
            "payment_entity_id" => $id,
            "client_id" => $appointment->client_id,
        ]);
        $session->save();
        $data = [
            "order_amount" => $appointment->total_amount,
            "order_id" => "ORD$session->id",
            "order_note" => "client_id_" . $appointment->client_id,
            "order_currency" => "INR",
            "customer_details" => [
                "customer_id" => "$appointment->created_by",
                "customer_name" => $user->getFullName(),
                "customer_email" => $user->email,
                "customer_phone" => $user->mobile_number
            ]
        ];
        $response = Http::withHeaders([
            'X-Client-Secret' => $secret,
            'X-Client-Id' => $clientId,
            'x-api-version' => '2022-09-01',
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ])->post($pgUrl, $data);


        $session->cf_order_id = $response["cf_order_id"];
        $session->order_id = $response["order_id"];
        $session->order_expiry_time = date("Y-m-d H:i:s", strtotime($response["order_expiry_time"]));
        $session->order_note = $response["order_note"];

        $session->payment_session_id = $response["payment_session_id"];
        $session->response = json_encode($response["cf_order_id"]);
        
        $session->save();

        $responseData = [];
        $responseData["payment_session_id"] = $session["payment_session_id"];
        $responseData["cf_order_id"] = $session["cf_order_id"];
        $responseData["order_id"] = $session["order_id"];

        return $responseData;
    }

    public function verifyPayment(Request $request){
        $data = $request->validate(["order_id"=>"required"]);
        $orderId = $data["order_id"];
        $pgUrl = env("CF_URL");
        $clientId = env("CF_CLIENT_ID");
        $secret = env("CF_CLIENT_SECRET");
        $mode = env("CF_MODE");
        $user = auth('sanctum')->user();

        $response = Http::withHeaders([
            'X-Client-Secret' => $secret,
            'X-Client-Id' => $clientId,
            'x-api-version' => '2022-09-01',
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ])->post($pgUrl."/$orderId", $data);

        if($response["order_status"] != "PAID")
        {
            return "PENDING";
        }

        $id = str_replace("ORD", "", $response["order_id"]);
        $session = CashFreeSession::find($id);
        $payment = new CashFreePayment();

        $payment->fill([
            "payment_session" => $id,
            "payment_status" => $response["order_status"],
            "created_by" => $user->id,
            "response" => json_encode($response),
            "client_id" => $session->client_id,
        ]);
        $payment->save();
        $this->updateAppointment($session->payment_entity_id, $payment->id);
        return $response["order_status"];

    }

    public function updateAppointment($id, $paymentId){
        $appointment = Appointment::find($id);
        $appointment->appointment_status = "booked";
        $appointment->payment_id = $paymentId;
        $appointment->save();
    }
}
