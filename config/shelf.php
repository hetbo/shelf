<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Disk
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default filesystem disk that should be used
    | by the shelf package for storing files.
    |
    */

    'default_disk' => env('SHELF_DEFAULT_DISK', 'public'),

    /*
    |--------------------------------------------------------------------------
    | File Upload Settings
    |--------------------------------------------------------------------------
    |
    | Configure file upload constraints and settings.
    |
    */

    'upload' => [
        'max_file_size' => env('SHELF_MAX_FILE_SIZE', 10485760), // 10MB in bytes
        'allowed_mimes' => [
            'image/jpeg',
            'image/png',
            'image/gif',
            'image/webp',
            'application/pdf',
            'text/plain',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ],
        'generate_thumbnails' => env('SHELF_GENERATE_THUMBNAILS', true),
        'thumbnail_sizes' => [
            'small' => [150, 150],
            'medium' => [300, 300],
            'large' => [800, 600],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Hash Algorithm
    |--------------------------------------------------------------------------
    |
    | The hash algorithm to use for file deduplication.
    |
    */

    'hash_algorithm' => env('SHELF_HASH_ALGORITHM', 'sha256'),

    /*
    |--------------------------------------------------------------------------
    | File Roles
    |--------------------------------------------------------------------------
    |
    | Define the available file roles for your application.
    |
    */

    'roles' => [
        'featured' => 'Featured Image',
        'gallery' => 'Gallery Images',
        'attachment' => 'Attachment',
        'document' => 'Document',
        'avatar' => 'Avatar',
        'banner' => 'Banner',
    ],

    /*
    |--------------------------------------------------------------------------
    | Folder Settings
    |--------------------------------------------------------------------------
    |
    | Configure folder behavior.
    |
    */

    'folders' => [
        'max_depth' => env('SHELF_MAX_FOLDER_DEPTH', 10),
        'auto_create_user_folders' => env('SHELF_AUTO_CREATE_USER_FOLDERS', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Security Settings
    |--------------------------------------------------------------------------
    |
    | Configure security-related settings.
    |
    */

    'security' => [
        'scan_for_malware' => env('SHELF_SCAN_MALWARE', false),
        'check_file_extensions' => env('SHELF_CHECK_EXTENSIONS', true),
        'sanitize_filenames' => env('SHELF_SANITIZE_FILENAMES', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Cleanup Settings
    |--------------------------------------------------------------------------
    |
    | Configure automatic cleanup of orphaned files.
    |
    */

    'cleanup' => [
        'delete_orphaned_files' => env('SHELF_DELETE_ORPHANED', false),
        'orphaned_file_age_days' => env('SHELF_ORPHANED_AGE_DAYS', 30),
        'cleanup_soft_deleted' => env('SHELF_CLEANUP_SOFT_DELETED', true),
        'soft_deleted_age_days' => env('SHELF_SOFT_DELETED_AGE_DAYS', 90),
    ],

];