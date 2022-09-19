<?php

namespace App\Repository;

use Idaas\Passport\Bridge\UserRepository;
use League\OAuth2\Server\Entities\UserEntityInterface;

class OIDCUserRepository extends UserRepository
{
    /**
     * Returns an associative array with attribute (claim) keys and values
     */
    public function getAttributes(UserEntityInterface $user, $claims, $scopes)
    {
        $subject = resolve(SubjectRepository::class)->get($user->getIdentifier());

        return $subject->toIDTokenClaims($claims, $scopes);
    }

    public function getUserInfoAttributes(UserEntityInterface $user, $claims, $scopes)
    {
        $subject = resolve(SubjectRepository::class)->get($user->getIdentifier());

        return $subject->toUserInfo($claims, $scopes);
    }
}
