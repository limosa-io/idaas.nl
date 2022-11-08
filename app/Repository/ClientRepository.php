<?php

namespace App\Repository;

use App\Client;
use App\Exceptions\NoClientException;
use App\Scopes\TenantScope;
use Idaas\Passport\ClientRepository as IdaasClientRepository;

class ClientRepository extends IdaasClientRepository
{
    protected $clientClass = Client::class;

    protected static $cache = [];

    public function findForManagement($id)
    {
        return $this->clientClass::with(['defaultAcrValues', 'roles', 'groups'])->find($id);
    }

    public function find($id)
    {
        //TODO: sure this is without TenantScope?? Not insecure?
        // phpcs:ignore Generic.Files.LineLength.TooLong
        $client =  self::$cache[$id] ??  self::$cache[$id] = $this->clientClass::withoutGlobalScope(TenantScope::class)->find($id);

        if ($client == null) {
            throw new NoClientException();
        }

        return $client;
    }
}
