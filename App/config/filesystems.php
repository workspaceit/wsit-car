<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default filesystem disk that should be used
    | by the framework. The "local" disk, as well as a variety of cloud
    | based disks are available to your application. Just store away!
    |
    */

    'default' => env('FILESYSTEM_DRIVER', 'local'),

    /*
    |--------------------------------------------------------------------------
    | Default Cloud Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | Many applications store files both locally and in the cloud. For this
    | reason, you may specify a default "cloud" driver here. This driver
    | will be bound as the Cloud disk implementation in the container.
    |
    */

    'cloud' => env('FILESYSTEM_CLOUD', 's3'),

    /*
    |--------------------------------------------------------------------------
    | Filesystem Disks
    |--------------------------------------------------------------------------
    |
    | Here you may configure as many filesystem "disks" as you wish, and you
    | may even configure multiple disks of the same driver. Defaults have
    | been setup for each driver as an example of the required options.
    |
    | Supported Drivers: "local", "ftp", "sftp", "s3", "rackspace"
    |
    */

    'disks' => [

        'local' => [
            'driver' => 'local',
            'root' => storage_path('app'),
        ],

        'logs' => [
            'driver' => 'local',
            'root' => storage_path('logs'),
        ],

        'public' => [
            'driver' => 'local',
            'root' => storage_path('app/public'),
            'url' => env('APP_URL').'/storage',
            'visibility' => 'public',
        ],

        'car-images' => [
            'driver' => 'local',
            'root' => 'images/cars',
            'url' => env('APP_URL').'/images/cars',
            'visibility' => 'public',
        ],

        'dealer-logos' => [
            'driver' => 'local',
            'root' => 'images/logos',
            'url' => env('APP_URL').'/images/logos',
            'visibility' => 'public',
        ],

        'dealer-issues' => [
            'driver' => 'local',
            'root' => 'images/issues',
            'url' => env('APP_URL').'/images/issues',
            'visibility' => 'public',
        ],

        'dealer-watermarks' => [
            'driver' => 'local',
            'root' => 'images/watermarks',
            'url' => env('APP_URL').'/images/watermarks',
            'visibility' => 'public',
        ],

        'tm-tasks' => [
            'driver' => 'local',
            'root' => 'tasks',
            'url' => env('APP_URL').'/tasks',
            'visibility' => 'public',
        ],

        'members' => [
            'driver' => 'local',
            'root' => 'images/members',
            'url' => env('APP_URL').'/images/members',
            'visibility' => 'public',
        ],

        'contract-documents' => [
            'driver' => 'local',
            'root' => 'images/documents',
            'url' => env('APP_URL').'/images/documents',
            'visibility' => 'public',
        ],

        's3' => [
            'driver' => 's3',
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'region' => env('AWS_DEFAULT_REGION'),
            'bucket' => env('AWS_BUCKET'),
            'url' => env('AWS_URL'),
        ],

        'csv-ftp' => [
            'driver'   => 'ftp',
            'host'     => '70.35.204.187',
            'username' => 'MonezsoftDealer',
            'password' => 'Monez~Dealer2019',
        ],

        'mtlautoprix-ftp' => [
            'driver'   => 'ftp',
            'host'     => '70.35.204.187',
            'username' => 'mtlautoprix',
            'password' => 'S{VC8)2k?y5@e:GK',
        ],

    ],

];
