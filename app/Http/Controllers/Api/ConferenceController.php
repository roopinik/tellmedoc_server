<?php

namespace App\Http\Controllers\Api;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Routing\Controller as BaseController;
use App\Models\HealthCareUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Models\LabReport;
use App\Helpers\RtcTokenBuilder;
use App\Models\Appointment;
use App\Models\AppointmentCheckin;

class ConferenceController extends BaseController
{
    public function getRecords(Request $request){
        $limit = $request->get("limit",10);
        $offset = $request->get("page",0)*$limit;
        $mobile = auth()->user()->mobile_number;
        return LabReport::with(["person:name,id,mrn,abha_id"])->where("mobile_number", $mobile)->paginate($limit);
    }

    public function getVideoCallPage(Request $request){
        return view('rtc.index', ['name' => 'James']);
    }


    // {
    //     "patient_id": "2",
    //     "checkinstatus": "checkedin",
    //     "call_status": "waiting",
    //     "checkin_id": "20",
    //     "apt_id": "63",
    //     "doctor": "14",
    //     "checked_in_at": "1711017861",
    //     "appointment_date": "2024-03-20",
    //     "schedule_slot": "17",
    //     "hospital_lab": "1",
    //     "appointment_status": "booked",
    //     "patient_name": "Patient 0",
    //     "patient_mobile": "9902233232",
    //     "doctor_name": "Dr. Anushka"
    //   }

    public function getDoctorCheckins(Request $request){
        $id = 3;//auth("sanctum")->user()->id;
        $appointment = Appointment::has("appointmentCheckins")
        ->with(['creator:id,first_name,mobile_number','doctor:id,first_name,last_name,profile_pic',"person:id,name,mrn,abha_id"])
        ->where("doctor_id", $id)
        // ->where("appointment_date",\Carbon\Carbon::now()->format('Y-m-d'))
        ->where("appointment_status", "booked")->get();
        return $appointment;
    }

    public function getCheckins(Request $request, $id){
        $appointment = Appointment::has("appointmentCheckins")
        ->with(['creator:id,first_name,mobile_number','doctor:id,first_name,last_name,profile_pic',"person:id"])
        ->where("doctor_id", $id)
        // ->where("appointment_date",\Carbon\Carbon::now()->format('Y-m-d'))
        ->where("appointment_status", "booked")->get();
        return $appointment;
    }

}
