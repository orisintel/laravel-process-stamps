<?php

return [

    'table' => 'process_stamps',

    'columns' => [
        'primary_key' => 'id',
        'created'     => 'process_created_id',
        'updated'     => 'process_updated_id',
    ],

    'cache' => [
        'enabled' => env('PROCESS_STAMP_CACHE_ENABLED', true),
        'store'   => env('PROCESS_STAMP_CACHE_DRIVER', 'redis'),
    ],
];
