<?php

namespace App\Services;

use GuzzleHttp\Client;

class LiveUpdatesService
{
    public $defaultFields = [
        [
            "name" => "appointment_date",
            "type" => "text",
            "required" => true,
            "max" => 12
        ],
        [
            "name" => "status",
            "type" => "text",
            "required" => true,
            "max" => 20
        ],
        [
            "name" => "token",
            "type" => "number",
            "required" => true,
            "max" => 1000
        ],
        [
            "name" => "hospital_id",
            "type" => "number",
            "required" => true,
            "max" => 10000
        ],
        [
            "name" => "doctor_id",
            "type" => "number",
            "required" => true,
            "max" => 10000
        ],
        [
            "name" => "created_by",
            "type" => "number",
            "required" => true,
            "max" => 10000
        ],
        [
            "name" => "created_by",
            "type" => "number",
            "required" => true,
            "max" => 10000
        ],

    ];
    public function createCollection($collectionName, $fields = null)
    {
        $url = "https://live.codebliss.in/create/collection";
        $guzzle = new Client([
            'headers' => [
                'content-type' => 'application/json',
                'accept' => 'application/json',
                'Authorization' => env('LIVETOKEN')
            ]
        ]);

        $data = [];
        $data["collection_name"] = $collectionName;
        if ($fields != null)
            $data["fields"] = $fields;
        else
            $data["fields"] = $this->defaultFields;
        $response = $guzzle->post(
            $url,
            [
                'body' => json_encode(
                    $data
                )
            ]
        );
        $r = $response->getBody()->getContents();
        $response = json_decode($r);
        return $response;
    }

    public function createRecord($collectionName, $inputs = null)
    {
        $url = "https://live.codebliss.in/create/record";
        $guzzle = new Client([
            'headers' => [
                'content-type' => 'application/json',
                'accept' => 'application/json',
                'Authorization' => env('LIVETOKEN')
            ]
        ]);

        $data = [];
        $data["collection_name"] = $collectionName;
        $data["data"] = $inputs;
        $response = $guzzle->post(
            $url,
            [
                'body' => json_encode(
                    $data
                )
            ]
        );
        $r = $response->getBody()->getContents();
        $response = json_decode($r);
        return $response;
    }
}


