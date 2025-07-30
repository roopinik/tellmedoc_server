<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    public function testEncryption(){
        $path = storage_path()."/app/public/634254d3-f1c4-4ffd-87ac-21d7510daf61/01J49HSCHEBZY1K8AS9YBK3Z3C.xlsx";
        $key = env("ENC_KEY");
        $contents = file_get_contents($path);
        $encrypted = $this->my_encrypt($contents, $key);
        file_put_contents($path, $encrypted);
    }

    public function testDecryption(){

    }

    function my_encrypt($data, $key) {
        $encryption_key = base64_decode($key);
        $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc'));
        $encrypted = openssl_encrypt($data, 'aes-256-cbc', $encryption_key, 0, $iv);
        return base64_encode($encrypted . '::' . $iv);
    }

    function my_decrypt($data, $key) {
        $encryption_key = base64_decode($key);
        list($encrypted_data, $iv) = explode('::', base64_decode($data), 2);
        return openssl_decrypt($encrypted_data, 'aes-256-cbc', $encryption_key, 0, $iv);
    }
}
