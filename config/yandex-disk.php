<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Yandex Disk Configuration
    |--------------------------------------------------------------------------
    |
    | OAuth token from https://oauth.yandex.ru/
    | Create app with "cloud_api:disk.read" and "cloud_api:disk.write" permissions
    |
    */
    
    'oauth_token' => env('YANDEX_DISK_TOKEN'),
    
    // Base folder for all uploads
    'base_folder' => env('YANDEX_DISK_FOLDER', '/LMS Videos'),
    
    // Cache download URLs for this many seconds
    'cache_ttl' => env('YANDEX_DISK_CACHE_TTL', 3600),
];
