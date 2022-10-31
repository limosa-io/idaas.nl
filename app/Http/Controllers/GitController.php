<?php

namespace App\Http\Controllers;

use App\AuthChain;
use App\AuthModule;
use App\Client as AppClient;
use App\CloudFunction;
use App\EmailTemplate;
use App\Git;
use App\HostedIdentityProvider;
use App\RemoteServiceProvider;
use App\SAMLConfig;
use App\TenantSetting;
use App\Translation;
use App\UIServer;
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
        RemoteServiceProvider::class,
        HostedIdentityProvider::class,
        UIServer::class,
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

            $git->push_start_time = now();
            $git->save();

            foreach (self::TYPES_TO_SYNC as $type) {
                $objects = $type::all()->values();
                foreach ($objects as $object) {
                    $type = strtolower((new \ReflectionClass($object))->getShortName());
                    $file = $object->id;

                    // The authentication module does not return the configuration by default as it is used by public API's
                    if ($object instanceof AuthModule) {
                        $object->withConfig();
                    }

                    $body = [
                        "owner" => $git->settings['owner'],
                        "repo" => $git->settings['repository'],
                        "path" => sprintf('/%s/%s.yaml', $type, $file),
                        "message" => "Sync",
                        "committer" => [
                        "name" => "Idaas Syncer",
                        "email" => "no-reply@idaas.nl"
                        ],
                        "content" => base64_encode(
                            json_encode($object, JSON_PRETTY_PRINT)
                        )
                    ];

                    $path = sprintf('%s/%s.yaml', $type, $file);

                    if (array_key_exists($path, $existing)) {
                        $body['sha'] = $existing[$path]['sha'];
                    }

                    if (!array_key_exists($path, $existing) || base64_decode($existing[$path]['content']) != base64_decode($body['content'])) {
                        $guzzle->put(sprintf('https://api.github.com/repos/%s/%s/contents/%s', $git->settings['owner'], $git->settings['repository'], $path), [
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

            $git->push_start_time = null;
            $git->save();
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
            sprintf('https://api.github.com/repos/%s/%s/contents/%s', $git->settings['owner'], $git->settings['repository'], $path),
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
           // throw new Exception("This is not a folder");
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

        return $result;
    }

    public function pull()
    {
        $git = $this->settings();
        $git->pull_start_time = now();
        $git->save();

        $files = $this->getFiles();

        foreach ($files as $path => $file) {
            $first = collect(self::TYPES_TO_SYNC)->first(fn($value) =>
                str_starts_with($file['path'], strtolower((new \ReflectionClass($value))->getShortName()) . '/'));

            if ($first != null) {
                $json_decode = json_decode(base64_decode($file['content']), true);

                if (!array_key_exists(app($first)->getKeyName(), $json_decode)) {
                    return $json_decode;
                }

                /** @var \Illuminate\Database\Eloquent\Model */
                $object = $first::find(
                    $json_decode[app($first)->getKeyName()]
                );
                $object->fill($json_decode);
                $object->save();
            }
        }

        $git->pull_start_time = now();
        $git->save();
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
