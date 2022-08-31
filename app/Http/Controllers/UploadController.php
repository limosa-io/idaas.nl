<?php

/**
 * Lists the access tokens in use. For management purposes.
 */

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Aws\S3\S3Client;

class UploadController extends Controller
{
    /**
     * Signs s3 uploads
     */
    public function s3sign(Request $request)
    {
        $config = [
            'version' => 'latest',
            'region' => config('s3.aws_region'),
            'credentials' => [
                'key'    => config('s3.aws_access_key_id'),
                'secret' => config('s3.aws_secret_access_key'),
            ],
        ];

        if (!empty(config('s3.endpoint'))) {
            $config['endpoint'] = config('s3.endpoint');
        }

        $s3 = new S3Client($config);

        $command = $s3->getCommand(
            'PutObject',
            [
                'Bucket' => config('s3.aws_bucket'),
                'Key' => config('s3.aws_directory') .
                    "/" . resolve('App\Tenant')->id .
                    '/' . $request->input('filename'),
                'ACL' => 'public-read',
                'ContentType' => $request->input('contentType'),
                'Body' => '',
            ]
        );

        $response = $s3->createPresignedRequest($command, '+5 minutes');

        return [
            'method' => $response->getMethod(),
            'url' => (string) $response->getUri(),
            'fields' => [],
        ];
    }
}
