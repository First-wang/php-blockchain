<?php

return [
    'default' => 'blockBucket',

    'stores' => [

        'blockBucket' => [
            'driver' => 'file',
            'path' => storage_path() . '/blockBucket',
        ],

        'utxoBucket' => [
            'driver' => 'file',
            'path' => storage_path() . '/utxoBucket',
        ]

    ],

    'prefix' => 'bc_'

];
