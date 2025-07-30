<?php

namespace App\Helpers;

class WhatsAppEncryption {

    private $rsaPrivKey;
    private $aes128key;

    function __construct(){
        $this->rsaPrivKey = "";
        $this->aes128key = env("");
    }
}
