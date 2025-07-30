<?php

use \App\Services\WAConnectService;
use \App\Models\Client;
$waService = new WAConnectService;

$waService->sendInteractiveMessage(
    Client::find(1),
    "919902233232",
    "Presenting you with a list of fruits",
    ["Apples", "Oranges", "Bananas"]
);

die();

$waService->sendInteractiveListMessage(
    Client::find(1),
    "919902233232",
    "Presenting you with a list of fruits",
    [
        "button_text" => "Select Fruit",
        "sections" => [
            [
                "title" => "Fruits 1",
                "rows" => [
                    ["row_id" => 1, "title" => "Orange", "description" => "Has Orange Color"],
                    ["row_id" => 2, "title" => "Apple", "description" => "Has Red Color"],
                    ["row_id" => 3, "title" => "Banana", "description" => "Has Yellow Color"],
                ]
            ],
            [
                "title" => "Fruits 2",
                "rows" => [
                    ["row_id" => 4, "title" => "Grapes", "description" => "Has Purple Color"],
                    ["row_id" => 5, "title" => "Jackfruit", "description" => "Has Green Color"],
                    ["row_id" => 6, "title" => "Kiwi", "description" => "Has Brown Color"],
                ]
            ]
        ]
    ]
);


// use \App\Services\LiveUpdatesService;

// $lus = new LiveUpdatesService;

// $data = [
//     "doctor_id" => 3,
//     "hospital_id" => 1,
//     "status" => "Completed",
//     "appointment_date" => "2025-02-18",
//     "token" => 23,
//     "created_by" => 1
// ];

// $lus->createRecord("live_stream_1", $data);

// $lus->createCollection("live_stream_1");


// use YorCreative\UrlShortener\Services\UrlService;

// $url = "https://mail.google.com/mail/u/0/#inbox";
// $url =  UrlService::shorten($url)
//     ->withExpiration(\Carbon\Carbon::now()->addDay()->timestamp)
//     ->withOpenLimit(5)->build();
