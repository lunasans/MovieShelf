<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default filesystem disk that should be used
    | by the framework. The "local" disk, as well as a variety of cloud
    | based disks are available to your application for file storage.
    |
    */

    'default' => env('FILESYSTEM_DISK', 'local'),

    /*
    |--------------------------------------------------------------------------
    | Filesystem Disks
    |--------------------------------------------------------------------------
    |
    | Below you may configure as many filesystem disks as necessary, and you
    | may even configure multiple disks for the same driver. Examples for
    | most supported storage drivers are configured here for reference.
    |
    | Supported drivers: "local", "ftp", "sftp", "s3"
    |
    */

    'disks' => [

        'local' => [
            'driver' => 'local',
            'root' => storage_path('app/private'),
            'serve' => true,
            'throw' => false,
            'report' => false,
        ],

        'public' => [
            'driver' => 'local',
            'root' => storage_path('app/public'),
            'url' => '/media',
            'visibility' => 'public',
            'throw' => false,
            'report' => false,
        ],

        'central' => [
            'driver' => 'local',
            'root' => base_path('storage/app/public'),
            'url' => '/media',
            'visibility' => 'public',
        ],

        's3' => [
            'driver' => 's3',
            'key' => env('S3_ACCESS_KEY_ID'),
            'secret' => env('S3_SECRET_ACCESS_KEY'),
            'region' => 'auto',
            'bucket' => env('S3_BUCKET'),
            'url' => env('S3_URL'),
            'endpoint' => env('S3_ENDPOINT') ?? (env('S3_ACCOUNT_ID') ? "https://" . env('S3_ACCOUNT_ID') . ".r2.cloudflarestorage.com" : null),
            'use_path_style_endpoint' => true,
            'throw' => true,
            'report' => false,
        ],

        'r2' => [
            'driver' => 's3',
            'key' => env('S3_ACCESS_KEY_ID'),
            'secret' => env('S3_SECRET_ACCESS_KEY'),
            'region' => 'auto',
            'bucket' => env('S3_BUCKET'),
            'url' => env('S3_URL'),
            'endpoint' => env('S3_ENDPOINT') ?? (env('S3_ACCOUNT_ID') ? "https://" . env('S3_ACCOUNT_ID') . ".r2.cloudflarestorage.com" : null),
            'use_path_style_endpoint' => true,
            'throw' => true,
            'report' => false,
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Symbolic Links
    |--------------------------------------------------------------------------
    |
    | Here you may configure the symbolic links that will be created when the
    | `storage:link` Artisan command is executed. The array keys should be
    | the locations of the links and the values should be their targets.
    |
    */

    'links' => [
        public_path('storage') => storage_path('app/public'),
    ],

];
