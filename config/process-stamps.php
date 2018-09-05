<?php

return [

    'table' => 'process_stamps',

    'columns' => [
        'created' => 'process_created_id',
        'updated' => 'process_updated_id',
    ],

    'cache' => [
        'enabled' => true,
        'store'   => env('PROCESS_STAMP_CACHE_DRIVER', 'redis'),
    ],

];
