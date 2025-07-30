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
use App\Models\AppointmentCheckin;
use App\Models\Person;

class AppointmentController extends BaseController
{


    public function getAppointments(Request $request)
    {
        $limit = $request->get("limit", 10);
        $offset = $request->get("page", 0) * $limit;
        $userId = auth("sanctum")->user()->id;
        $personIds = Person::where("created_by", $userId)->get()->pluck("id");
        $appointments = Appointment::with(["person:id,name", "doctor:id,first_name,last_name,email,profile_pic", "doctor.specializations:id,name"])->where("appointment_status", "booked")->whereIn("person_id", $personIds)->paginate($limit);
        return $appointments;
    }

    public function createAppointment(Request $request)
    {
        $data = $request->validate([
            "selectedSlot" => "required",
            "selectedDate" => "required",
            "selectedPatient" => "required",
            "doctor" => "required",
            "type" => "required"
        ]);
        $user = auth("sanctum")->user();
        $slot = $data["selectedSlot"];
        $selectedDate = $data["selectedDate"];
        $selectedPatient = $data["selectedPatient"];
        $doctorId = $data["doctor"];
        $type = $data["type"];
        $cost = $this->getDoctorAppointmentCost($doctorId, $type);
        $person = Person::find($selectedPatient);
        $appointment = new Appointment();
        $appointment->uuid = Str::uuid();
        $appointment->appointment_date = $selectedDate;
        $appointment->schedule_time = $slot;
        $appointment->client_id = $user->getClientId();
        $appointment->appointment_type = $type;
        $appointment->appointment_status = "pending";
        $appointment->doctor_id = $doctorId;
        $appointment->total_amount = $cost;
        $appointment->abha_id = $person->abha_id;
        $appointment->mrn = $person->mrn;
        $appointment->person_id = $selectedPatient;
        $appointment->account_id = $user->id;
        $appointment->created_by = $user->id;
        $appointment->save();
        return $appointment;
    }

    public function checkIn(Request $request, $id)
    {
        $userId = auth("sanctum")->user()->id;
        $appointment = Appointment::where("created_by", $userId)->where("id", $id)->first();

        if ($appointment == null) {
            return response()->json(["status" => 404, "data" => null, "message" => "Appointment not found."], 404);
        }

        $checkin = new AppointmentCheckin();
        $checkin->uuid = Str::uuid();
        $checkin->appointment_id = $appointment->id;
        $checkin->doctor_id = $appointment->doctor_id;
        $checkin->checked_in_at = Carbon::now();
        $checkin->created_by = $userId;
        $checkin->client_id = $appointment->client_id;
        $checkin->call_status = "checkedin";
        $checkin->save();
        return ["status" => 200, "message" => "user checked in.", "data" => $checkin->checked_in_at];
    }

    public function cancelAppointment(Request $request, $id)
    {
        $userId = auth()->user()->id;
        $appointment = Appointment::where("created_by", $userId)->where("id", $id)->first();
        if ($appointment == null) {
            return response()->json(["status" => 404, "data" => null, "message" => "Appointment not found."], 404);
        }

        $refund = new \App\Models\CFRefund();
        $refund->client_id = $appointment->client_id;
        $refund->entity_id = $appointment->id;
        $refund->module = "appointment";
        $refund->save();
        $appointment->appointment_status = "cancelled";
        $appointment->save();
        return ["status" => 200, "message" => "appointment cancelled refund initiated.", "data" => null];
    }
    public function getDoctorAppointmentCost($id, $type)
    {
        return HealthCareUser::find($id)[$type . "_appointment_cost"];
    }
}
