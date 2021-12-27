<?php

namespace App\Repository;

use Idaas\Passport\Bridge\ClaimRepository as IdaasClaimRepository;

class ClaimRepository extends IdaasClaimRepository
{
    public function getScopeClaims()
    {
        $scopeClaims = parent::getScopeClaims();

        $scopeClaims['roles'] = ['roles'];
        $scopeClaims['applications:manage'] = ['roles'];
        $scopeClaims['applications:read'] = ['roles'];

        return $scopeClaims;
    }
}
