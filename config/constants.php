<?php

return [
    'minio_access_key' => env('MINIO_ACCESS_KEY', 'AKIAIOSFODNN7EXAMPLE'),
    'minio_secret_key' => env('MINIO_SECRET_KEY', 'wJalrXUtnFEMI/K7MDENG/bPxRfiCYEXAMPLEKEY'),
    'minio_endpoint' => env('MINIO_ENDPOINT', 'http://minio.core.greenlabs.ai'),
    'minio_bucket' => env('MINIO_BUCKET', 'local'),
    'minio_folder' => env('MINIO_FOLDER', 'face_reg'),
];
