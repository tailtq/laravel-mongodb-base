<?php

return [
    'MINIO_ACCESS_KEY' => env('MINIO_ACCESS_KEY', 'AKIAIOSFODNN7EXAMPLE'),
    'MINIO_SECRET_KEY' => env('MINIO_SECRET_KEY', 'wJalrXUtnFEMI/K7MDENG/bPxRfiCYEXAMPLEKEY'),
    'MINIO_MINIO_ENDPOINT' => env('MINIO_MINIO_ENDPOINT', 'http://minio.core.greenlabs.ai'),
    'MINIO_BUCKET' => env('MINIO_BUCKET', 'local'),
    'MINIO_FOLDER' => env('MINIO_FOLDER', 'face_reg'),
];
