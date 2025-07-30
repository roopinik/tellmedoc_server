<?php

namespace App\Http\Controllers\Api;

use Illuminate\Routing\Controller as BaseController;
use App\Models\DoctorLeave;

class DoctorLeaveController extends BaseController
{
    public function fetchUpcomingLeaves()
    {
        $user = auth()->user();
        $leaves = DoctorLeave::where("created_by", $user->id)->orderBy("created_at", "desc")->get();
        return $leaves;
    }

    public function toggleStatus()
    {
        $id = request("id", null);
        $value = request("value", null);
        $user = auth()->user();
        $doctorLeave = DoctorLeave::find($id);
        if ($user->id != $doctorLeave->doctor_id)
            return response()->json(["error" => "Doctor leave not found"], 404);
        $doctorLeave->enabled = $value;
        $doctorLeave->save();
        return $doctorLeave;
    }

    public function createLeave()
    {
        $user = auth()->user();
        $doctorId = request("doctor_id", null);
        $date = request("date", null);
        $hospitalId = request("hospital_id", "");
        $timeRange = request("time_range", null);
        $reason = request("reason", null);
        $timeRange = collect($timeRange)->map(fn($i) => substr($i, 0, -4))->toArray();
        $leave = new DoctorLeave();
        $leave->doctor_id = $doctorId;
        $leave->client_id = $user->client_id;
        $leave->date = $date;
        $leave->hospital_id = $hospitalId;
        $leave->reason = $reason;
        $leave->timeranges = json_encode($timeRange);
        $leave->enabled = true;
        $leave->allday = request("isallday");
        $leave->save();
        return $leave;
    }

    public function deleteLeave()
    {
        $id = request('id');
        $user = auth()->user();
        
        $doctorLeave = DoctorLeave::find($id);
        
        if (!$doctorLeave) {
            return response()->json(['success' => false, 'error' => 'Leave not found'], 404);
        }

        if ($user->id != $doctorLeave->doctor_id) {
            return response()->json(['success' => false, 'error' => 'Unauthorized'], 403);
        }

        $doctorLeave->delete();
        
        return response()->json(['success' => true]);
    }
}