<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class WhatsAppFlowController extends Controller
{
    public function index(Request $request, $uuid){
        return base64_encode(json_encode(["status"=>"success"]));
    }

    public function testEncryption(){
        $string = "Hi How are you ?";
        return $string;
    }
}
