<?php

namespace App\Http\Controllers\Api;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Routing\Controller as BaseController;
use App\Models\Person;
use App\Models\UserDocument;
use App\Models\DeletedFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Models\Client;
use Illuminate\Support\Facades\Log;

class UserDocumentController extends BaseController
{
    public function getRecords(Request $request){
        $limit = $request->get("limit",10);
        $offset = $request->get("page",0)*$limit;
        $records = UserDocument::where("created_by", auth('sanctum')->user()->id)->take($limit)->skip($offset)->get();
        return ["status" => 200, "data" => $records, "message" => "Documents fetched."];
    }

    public function saveDocument(Request $request){
        $data = $request->validate([
            "id"=>"",
            "title"=>"required",
            "description"=>"required",
            "user"=>"required",
            "documents"=>""
        ]);
        if(isset($data["id"])){
            $userDocument = UserDocument::find($data["id"]);
        }
        else{
            $userDocument = new UserDocument();
        }
        $userDocument->title = $data["title"];
        $userDocument->description = $data["description"];
        $userDocument->person_id = $data["user"];
        $userDocument->created_by = auth('sanctum')->user()->id;
        $userDocument->client_id = auth('sanctum')->user()->getClientId();

        $documents  = [];

        foreach($data["documents"] as $document){
            if($document["deleted"]==true){
                DeletedFile::create([
                    "module"=>"user_documents",
                    "file"=>$document["id"]
                ]);
            }
            else{
                $documents[]=$document["id"];
            }
        }
        $userDocument->documents = json_encode($documents);
        $userDocument->save();
        return ["status" => 200, "data" => $userDocument, "message" => "Document created successfully."];
    }

    public function uploadFile(Request $request){
        $file = $request->file("file");
        $extension = $file->getClientOriginalExtension();
        $filename = Str::uuid().'.'.$extension;
        $file->move(public_path('../encrypted/user_documents'), $filename);
        return $filename;
    }
}