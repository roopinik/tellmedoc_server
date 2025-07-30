<?php

namespace App\Http\Controllers\Api;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Routing\Controller as BaseController;
use App\Models\Person;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Models\Client;

class AccountController extends BaseController
{
    public function getAccounts(Request $request){
        $limit = $request->get("limit",10);
        $offset = $request->get("page",0)*$limit;
        $user = auth("sanctum")->user();
        $mobile = $user->mobile_number;
        return Person::where("created_by",$user->id)->take($limit)->skip($offset)->get();
    }

    public function deletePerson(Request $request){
        $data = $request->validate([
            "id"=>"required"
        ]);
        $id = $data["id"];
        Person::where("created_by",auth("sanctum")->user()->id)->where("id",$id)->delete();

        return ["status" => 200, "data" => null, "message" => "Records deleted successfully."];
    }


    public function createPerson(Request $request){
        $data = $request->validate([
            "name" => "required", 
            "gender" => "required",
            "date_of_birth" => "required",
            "mrn"=> "",
            "abha_id"=> "",
            "is_primary" => "required",
        ]);
        $person = new Person();
        $person->name = $data["name"];
        $person->gender = $data["gender"];
        $person->date_of_birth = $data["date_of_birth"];
        $person->mrn = $data["mrn"];
        $person->abha_id = $data["abha_id"];
        $person->is_primary = $data["is_primary"];
        $person->created_by = auth("sanctum")->user()->id;
        $person->client_id = auth("sanctum")->user()->client_id;
        $person->save();
        return ["status" => 200, "data" => $person, "message" => "person created successfully."];
    }
}
