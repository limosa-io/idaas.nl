<?php

namespace Tests;

use App\Client;
use App\Console\Commands\NewMaster;
use App\Http\Middleware\DetectTenant;
use App\OAuthScope;
use App\Repository\AccessTokenRepository;
use App\Repository\KeyRepository;
use App\Subject;
use App\User;
use DateInterval;
use Idaas\Passport\Bridge\ClientRepository;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Passport\Passport;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function setUp(): void
    {

        parent::setUp();

        // Creates the master tenant
        $tenant = NewMaster::createTenant(
            'master',
            null,
            true,
            function () {
                return User::create(
                    [
                        'email' => 'arietimmerman@gmail.com',
                        'password' => Hash::make('1234'),
                    ]
                );
            }
        );

        DetectTenant::activateTenant($tenant);
    }

    public function getAccessToken()
    {
        /**
         * @var AccessTokenRepository
         */
        $accessTokenRepository = resolve(AccessTokenRepository::class);

        $c = Client::first();

        $client = resolve(ClientRepository::class)->getClientEntity($c->client_id, 'authorization_code', null, false);

        $scopes = OAuthScope::all()->pluck('description', 'name')->all();

        Passport::tokensCan($scopes);

        // applications:manage
        // resolve(ScopeRepository::class)->getScopeEntityByIdentifier('openid');
        // FIXME: ->identifier is set. but should also set ->id

        $accessToken = $accessTokenRepository->getNewToken($client, [], '1');
        $accessToken->setExpiryDateTime((new \DateTimeImmutable())->add(new DateInterval('PT1H')));
        $accessToken->setClient($client);

        $accessToken->addScope(new \Laravel\Passport\Bridge\Scope('applications:manage'));

        $user = User::first();

        // var_dump($user->id);exit;
        $eloquentSubject = Subject::firstOrCreate(
            [
                'id' => (string) Str::orderedUuid(),
            ],
            [
                'identifier' => (string) Str::orderedUuid(),
                'user_id' => $user->id,
                'subject' => null,
                'levels' => ['test'],
            ]
        );

        $accessToken->setUserIdentifier($eloquentSubject->id);
        $accessToken->setExpiryDateTime((new \DateTimeImmutable())->add(new DateInterval('PT1H')));
        $accessToken->setIdentifier(Str::random(40));

        $accessTokenRepository->persistNewAccessToken($accessToken);

        $privateKey = resolve(KeyRepository::class)->getPrivateKey();

        return (string) $accessToken->convertToJWT($privateKey)->toString();
    }
}
