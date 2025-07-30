<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\Request;
use App\Services\EncryptionService;
use Symfony\Component\HttpFoundation\Response;

class SharedDocumentController extends BaseController
{
    public function downloadDocument(Request $request, $clientId, $file)
    {
        $this->checkAccess($clientId, $file);
        $es = new EncryptionService;
        $key = env("ENC_KEY");
        $path = "shared-documents/$clientId/$file";
        $path = storage_path("/app/public/" . $path);
        $content = file_get_contents($path);
        $file = $es->my_decrypt($content, $key);
        return response($file, 200, [
            'Content-Type' => 'application/octet-stream',
        ]);
    }

    public function downloadDocumentOnWhatsApp(Request $request, $clientId, $file)
    {
        $es = new EncryptionService;
        $key = env("ENC_KEY");
        $clientId = base64_decode($clientId);
        $file = $es->my_decrypt(base64_decode($file),$key);
        $path = "shared-documents/$clientId/$file";
        $path = storage_path("/app/public/" . $path);
        $content = file_get_contents($path);
        $data = $es->my_decrypt($content, $key);
        return response($data, 200, [
            'Content-Type' => 'application/octet-stream',
            'Content-disposition'=>'attachment; filename="'.$file.'"'
        ]);
    }

    public function checkAccess($clientId, $file){
       $user = auth("filament")->user();
       if($user!=null){
        if($user->client->id == $clientId)
        return true;
       }
       else {
        abort(404);
       }
    }

}
