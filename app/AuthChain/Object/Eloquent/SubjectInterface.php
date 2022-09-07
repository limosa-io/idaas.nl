<?php

namespace App\AuthChain\Object\Eloquent;

use Illuminate\Contracts\Auth\Authenticatable;

interface SubjectInterface
{
    public function toUserInfo();

    public function getIdentifier();

    /**
     * @return Type
     */
    public function getTypeIdentifier();

    public function getApprovedScopes(?string $appId);

    public function getEmail();

    public function getPreferredLanguage();

    public function getMobile();

    public function getUserId();

    public function getUuid();

    public function isActive();

    public function setUserId(?string $userId);

    public function getAttributes();
}
