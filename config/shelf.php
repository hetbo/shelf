<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default Disk
    |--------------------------------------------------------------------------
    | The default disk to store files when none is specified
    */
    'default_disk' => env('SHELF_DISK', 'public'),

    /*
    |--------------------------------------------------------------------------
    | Files Table Name
    |--------------------------------------------------------------------------
    | The name of the table that will store file information
    */
    'table_name' => 'files',
];