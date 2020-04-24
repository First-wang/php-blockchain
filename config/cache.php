<?php

return [
    'default' => 'file',

    'stores' => [

        'file' => [
            'driver' => 'file',
            'path' => storage_path(),
        ],

    ],

    'prefix' => 'bc_'

];
