<?php

namespace App\Repository;

use App\Exceptions\NoKeyException;
use App\OpenIDKey;
use App\Tenant;
use Idaas\OpenID\CryptKey;
use Idaas\Passport\KeyRepository as IdaasKeyRepository;
use Idaas\Passport\Model\Client;
use Illuminate\Support\Facades\Cache;

class KeyRepository extends IdaasKeyRepository
{
    public function getPrivateKey()
    {
        return Cache::remember(
            sprintf('key:%s', resolve(Tenant::class)->id),
            10,
            function () {
                $primary = OpenIDKey::where('active', true)->first();

                if ($primary == null) {
                    throw new NoKeyException('No key found');
                }

                return (new CryptKey(
                    $primary->private_key
                ))->setKid($primary->id)->setX509($primary->x509);
            }
        );
    }

    public function getPublicKey()
    {
        return Cache::remember(
            sprintf('key:pub:%s', resolve(Tenant::class)->id),
            10,
            function () {
                $primary = OpenIDKey::where('active', true)->first();

                if ($primary == null) {
                    throw new NoKeyException('No key found');
                }

                return (new CryptKey(
                    $primary->public_key
                ))->setKid($primary->id)->setX509($primary->x509);

            }
        );
    }

    public function getPublicKeyForClient(Client $client, $kid = null)
    {
        return $this->getPublicKey();
    }

    public function getAllPublicKeys()
    {
        return [$this->getPublicKey()];
    }

    public function getPrivateKeyByKid($kid)
    {
        return $this->getPrivateKey();
    }
}
