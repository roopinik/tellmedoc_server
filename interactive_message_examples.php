<?php

use \App\Services\WAConnectService;
use \App\Models\Client;
$waService = new WAConnectService;

// 'interactive_type' => 'button',
// 'media_link' => '',
// 'header_type' => '', // "text", "image", or "video"
// 'header_text' => '',
// 'body_text' => '',
// 'footer_text' => '',
// 'buttons' => [
// ],
// 'cta_url' => null,
// 'action' => null,
// 'list_data' => null,

// $list_data = [["row_id"=>1, "title"=>"Title", "description"=>"Description"]]

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