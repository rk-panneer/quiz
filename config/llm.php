<?php

return [
    'default' => env('LLM_DRIVER', 'gemini'),

    'drivers' => [
        'gemini' => [
            'api_key' => env('GEMINI_API_KEY'),
            'model' => env('GEMINI_MODEL', 'gemini-pro'),
            'embedding_model' => env('GEMINI_EMBEDDING_MODEL', 'text-embedding-004'),
        ],
    ],

    'embedding_driver' => env('LLM_EMBEDDING_DRIVER', 'gemini'),
];
