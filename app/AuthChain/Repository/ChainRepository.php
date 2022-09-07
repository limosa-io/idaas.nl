<?php

/**
 * An authentication chain is a connected set of authentication modules, structued in a directed graph.
 */

namespace App\AuthChain\Repository;

use App\AuthChain\Config\Config;
use App\AuthChain\Exceptions\ApiException;
use App\AuthChain\Module\Chain;
use App\AuthChain\Module\ChainInterface;

class ChainRepository implements ChainRepositoryInterface
{
    /**
     * @return ChainInterface[]
     */
    public function all()
    {
        $chain = Config::getInstance()->get('authchain.chain');

        foreach ($chain as $c) {
            yield new Chain($c['from'], $c['to']);
        }
    }

    public function get($id)
    {
        throw new ApiException('Operation not supported');
    }

    public function add($from, $to, $treePosition)
    {
        throw new ApiException('Operation not supported');
    }

    /**
     *
     */
    public function delete(ChainInterface $chain)
    {
        throw new ApiException('Operation not supported');
    }

    public function exists($from, $to)
    {
        throw new ApiException('Operation not supported');
    }
}
