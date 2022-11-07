<?php

namespace App\CloudFunction;

use App\CloudFunction;
use App\CloudFunction\HandlerInterface;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\File;
use GuzzleHttp\RequestOptions;
use GuzzleHttp\Exception\RequestException;
use App\Exceptions\CloudFunctionException;
use App\Group;
use App\User;
use ArieTimmerman\Laravel\SCIMServer\Helper;
use ArieTimmerman\Laravel\SCIMServer\ResourceType;

class OpenWhiskHandler implements HandlerInterface
{
    public static function getActionUrl(CloudFunction $cloudFunction)
    {
        return sprintf(
            'https://%s/api/v1/namespaces/_/actions/%s?overwrite=true',
            config('serverless.openwhisk_host'),
            \urlencode($cloudFunction->name)
        );
    }

    public function deploy(CloudFunction $cloudFunction)
    {
        $response = null;
        $guzzle = new Client();

        $guzzle = new Client();
        $zip = new \ZipArchive();

        if ($zip->open('/dev/shm/test.zip', \ZipArchive::CREATE) !== true) {
            throw new \RuntimeException('Cannot open memory');
        }

        $path = resource_path('serverless/digitalocean/example');

        foreach (File::allFiles($path) as $path) {
            $zip->addFile($path, ltrim($path->getRelativePath() . '/' . $path->getFilename(), '/'));
        }

        $zip->addFromString('main.js', $cloudFunction->code);
        $zip->close();

        $contents = file_get_contents('/dev/shm/test.zip');
        $result = base64_encode($contents);

        $response = $guzzle->request(
            'PUT',
            self::getActionUrl($cloudFunction),
            [
                RequestOptions::HEADERS => [
                    'Authorization' => 'Basic ' . \base64_encode(config('serverless.openwhisk_api_key'))
                ],
                RequestOptions::JSON => [

                    "namespace" => "_",

                    "exec" => [

                        "kind" => "nodejs:16",
                        "binary" => true,
                        "code" => $result,

                        "components" => [
                            // action names
                        ]
                    ],

                    "limits" => [
                        "timeout" => 6000, // in miliseconds,
                        "memory" => 128, // in MB
                        "logs" => 10, // in MB
                    ],
                ]
            ]
        );

        // TODO: update cloudFunction last_deployed

        return $response;
    }
    public function invoke(CloudFunction $cloudFunction, $arguments)
    {
        if ($cloudFunction->needsDeploy()) {
            self::deploy($cloudFunction);
        }

        $parameters['variables'] = [];

        foreach ($cloudFunction->getMembers() as $member) {
            $counts = [];

            $parameters['variables'][$member->id] = collect($member->variables)->mapWithKeys(
                function ($value) use (&$counts) {
                    $result = null;

                    switch ($value['type']) {
                        case "EmailTemplate":
                            $result = $value['id'];
                            break;
                        case "User":
                            $result = Helper::objectToSCIMArray(User::find($value['id']), ResourceType::user());
                            break;
                        case "Group":
                            $result = Helper::objectToSCIMArray(Group::find($value['id']));
                            break;
                    }

                    if (!array_key_exists($value['type'], $counts)) {
                        $counts[$value['type']] = 0;
                    }

                    return [\lcfirst($value['type']) . ($counts[$value['type']]++) => $result];
                }
            )->toArray();
        }

        $actionUrl = sprintf(
            'https://%s/api/v1/namespaces/_/actions/%s?blocking=true&result=true',
            config('serverless.openwhisk_host'),
            \urlencode($cloudFunction->name)
        );
        $triedDeployment = false;
        $success = false;
        $guzzle = new Client();

        do {
            try {
                $response = $guzzle->request(
                    'POST',
                    $actionUrl,
                    [
                        RequestOptions::HEADERS        => [
                            'Authorization' => 'Basic ' . \base64_encode(config('serverless.openwhisk_api_key')),
                            'Content-Type' => 'application/json'
                        ],

                        // do NOT force_object, since this convers real arrays (with numeric indexes) to objects
                        RequestOptions::BODY => json_encode($parameters, JSON_FORCE_OBJECT)
                    ]
                );


                $success = true;
            } catch (RequestException $e) {
                $response = $e->getResponse();

                if ($response == null) {
                    throw $e;
                }

                if ($response->getStatusCode() == '404') {
                    self::deploy($cloudFunction);
                    $triedDeployment = true;
                } elseif ($response->getStatusCode() == '502') {
                    throw new CloudFunctionException((string) $response->getBody());
                } else {
                    throw $e;
                }
            }
        } while (!$triedDeployment && !$success);

        $cloudFunction->run_at = \Carbon\Carbon::now();
        $cloudFunction->save();

        $result = json_decode((string) $response->getBody(), true);

        return $result;
    }
}
