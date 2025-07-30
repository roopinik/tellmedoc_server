<?php

namespace App\Http\Controllers\Api;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Routing\Controller as BaseController;
use App\Models\HealthCareUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Models\Prescription;

class PrescriptionController extends BaseController
{
    public function getRecords(Request $request){
        $limit = $request->get("limit",10);
        $offset = $request->get("page",0)*$limit;
        $mobile = auth()->user()->mobile_number;
        return Prescription::with(["person:name,id,mrn,abha_id"])->where("mobile_number", $mobile)->paginate($limit);
    }
}
