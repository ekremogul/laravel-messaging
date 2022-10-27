<?php

// config for Ekremogul/LaravelMessaging
return [
    "user_model" => "App\Models\User",
    "filter" => [
        "enable" => false,
        "bad_word" => [
            "replace_with" => "*",
            "list" => [

            ]
        ],
        "email_filtered" => [
            "enable" => false,
            "replace_with" => " ##Sharing e-mail is prohibited## "
        ]
    ]
];
