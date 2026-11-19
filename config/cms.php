<?php

return [
    'api' => [
        'prefix' => 'api/v1',
    ],

    'authentication' => [
        'max_failed_attempts' => 5,
        'lockout_minutes' => 15,
        'idle_session_minutes' => 30,
        'password_min_length' => 8,
    ],

    'content' => [
        'supported_languages' => ['id', 'en', 'zh'],
        'default_language' => 'id',
        'scheduled_publication_enabled' => false,
        'soft_deletes' => true,
    ],

    'products' => [
        'featured_per_category' => 6,
        'catalog_per_page' => 12,
    ],

    'documentation' => [
        'per_page' => 9,
        'max_gallery_items' => 30,
    ],

    'media' => [
        'disk' => env('CMS_MEDIA_DISK', 'public'),
        'max_image_size_kilobytes' => 2048,
        'allowed_image_types' => ['jpg', 'jpeg', 'png', 'webp'],
    ],

    'activity_logs' => [
        'view_roles' => ['superadmin'],
        'retention_months' => 12,
    ],

    'phase_two' => [
        'oneis' => false,
        'leaflet' => false,
    ],
];
