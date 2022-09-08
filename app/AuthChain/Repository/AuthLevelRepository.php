<?php

/**
 * An authentication chain is a connected set of authentication modules, structued in a directed graph.
 */

namespace App\AuthChain\Repository;

use App\AuthChain\AuthLevelInterface;
use App\AuthChain\AuthLevel;
use App\AuthChain\Exceptions\ApiException;

class AuthLevelRepository
{
    /**
     * @return AuthLevelInterface[]
     */
    public function all()
    {
        throw new ApiException('Operation not supported');
    }

    /**
     * @return AuthLevelInterface
     */
    public function getByValue($value, $type)
    {
        throw new ApiException('Operation not supported');
    }

    /**
     * @return AuthLevelInterface
     */
    public function get($id)
    {
        throw new ApiException('Operation not supported');
    }

    /**
     * @return AuthLevelInterface
     */
    public function add($level, $type)
    {
        throw new ApiException('Operation not supported');
    }

    /**
     * @return AuthLevelInterface
     */
    public function save(AuthLevelInterface $authLevel)
    {
        throw new ApiException('Operation not supported');
    }

    /**
     *
     */
    public function delete(AuthLevelInterface $authLevel)
    {
        throw new ApiException('Operation not supported');
    }

    public function fromJson($json)
    {
        return AuthLevel::fromJsonObject($json);
    }
}
