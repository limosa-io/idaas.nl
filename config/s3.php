<?php

return [
    'endpoint' => env('S3_ENDPOINT'),
    'aws_region' => env('S3_AWS_REGION'),
    'aws_version' => env('S3_AWS_VERSION', 'latest'),
    'aws_directory' => env('S3_AWS_DIRECTORY', 'uploads'),
    'aws_bucket' => env('S3_AWS_BUCKET'),
    'aws_access_key_id' => env('S3_AWS_ACCESS_KEY_ID'),
    'aws_secret_access_key' => env('S3_AWS_SECRET_ACCESS_KEY'),
];
