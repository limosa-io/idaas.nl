<?php

namespace App\Http\Controllers;

use App\AuthChain;
use App\AuthModule;
use App\Client as AppClient;
use App\CloudFunction;
use App\EmailTemplate;
use App\Git;
use App\SAMLConfig;
use App\TenantSetting;
use App\Translation;
use Exception;
use Illuminate\Http\Request;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;

class GitController extends Controller
{
    protected $validations;

    public const TYPE_GITHUB = 'github';

    public const TYPES_TO_SYNC = [
        AuthChain::class,
        AppClient::class,
        AuthModule::class,
        TenantSetting::class,
        EmailTemplate::class,
        UIServer::class,
        AppServiceProvider::class,
        SAMLConfig::class,
        CloudFunction::class,
        Translation::class
    ];

    public function __construct()
    {
        $this->validations = [
            'type' => 'in:github,none',
            'settings' => 'array'
        ];
    }

    public function push()
    {
        $git = $this->settings();

        if ($git->type == self::TYPE_GITHUB) {
            $guzzle = new Client();

            $existing = $this->getFiles();

            foreach (self::TYPES_TO_SYNC as $type) {
                $objects = $type::all()->values();
                foreach ($objects as $object) {
                    $type = strtolower((new \ReflectionClass($object))->getShortName());
                    $file = $object->id;

                    $body = [
                        "owner" => "arietimmerman",
                        "repo" => "idaas-config",
                        "path" => sprintf('/%s/%s.yaml', $type, $file),
                        "message" => "Sync",
                        "committer" => [
                        "name" => "Idaas Syncer",
                        "email" => "no-reply@idaas.nl"
                        ],
                        "content" => base64_encode(json_encode($object, JSON_PRETTY_PRINT))
                    ];

                    $path = sprintf('%s/%s.yaml', $type, $file);

                    if (array_key_exists($path, $existing)) {
                        $body['sha'] = $existing[$path]['sha'];
                    }

                    if (!array_key_exists($path, $existing) || base64_decode($existing[$path]['content']) != base64_decode($body['content'])) {
                        $response = $guzzle->put(sprintf('https://api.github.com/repos/arietimmerman/idaas-config/contents/%s', $path), [
                        RequestOptions::HEADERS => [
                        'Authorization' => 'Bearer ' . $git->settings['token'],
                        'Accept' => 'application/vnd.github+json'
                        ],
                        RequestOptions::BODY => json_encode(
                            $body
                        )
                        ]);
                    }
                }
            }
        }
    }

    public function getContents($path)
    {
        $git = $this->settings();
        $result = [];

        if ($git->type != self::TYPE_GITHUB) {
            throw new Exception("Unknown git provider");
        }

        $guzzle = new Client();

        $response = $guzzle->get(
            sprintf('https://api.github.com/repos/arietimmerman/idaas-config/contents/%s', $path),
            [
                RequestOptions::HEADERS => [
                    'Authorization' => 'Bearer ' . $git->settings['token'],
                    'Accept' => 'application/vnd.github+json'
                ]
            ]
        );

        $result = json_decode((string) $response->getBody(), true);

        return $result;
    }

    public function getFiles()
    {
        $git = $this->settings();
        $result = [];

        if ($git->type != self::TYPE_GITHUB) {
            throw new Exception("not supported provider");
        }

        $contents = $this->getContents('');

        if (!is_array($contents)) {
            throw new Exception("This is not a folder");
        }

        foreach ($contents as $directory) {
            if ($directory['type'] == 'dir') {
                    $files = $this->getContents($directory['path']);

                foreach ($files as $file) {
                    $f = $this->getContents($file['path']);

                    if ($f['type'] == 'file') {
                        $result[$f['path']] = $f;
                    }
                }
            }
        }

        // TODO: updating existing

        return $result;
    }

    public function pull()
    {
        $files = $this->getFiles();

        foreach ($files as $path => $file) {
            // TODO; should be contains
            if (str_starts_with($file['path'], 'authmodule/')) {
                $json_decode = json_decode(base64_decode($file['content']), true);
                /** @var AuthModule */
                $object = AuthModule::find($json_decode['id']);
                $object->fill($json_decode);
                $object->save();
            }
        }
    }

    public function settings(): Git
    {
        return Git::all()->first() ?? new Git();
    }

    public function update(Request $request)
    {
        $data = $this->validate($request, $this->validations);

        $git = Git::all()->first();

        if ($git == null) {
            $git = new Git();
        }

        $git->forceFill($data);
        $git->save();
    }
}
