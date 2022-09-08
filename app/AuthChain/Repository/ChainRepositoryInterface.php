<?php

namespace App\AuthChain\Repository;

use App\AuthChain\Module\ChainInterface;

interface ChainRepositoryInterface
{
    public function all();

    /**
     * @return ChainInterface
     */
    public function get($id);

    /**
     * from module, to module.
     *
     * treePosition is purely informative. 'start' is position 0. The modules after start are position 1, etc.
     */
    public function add($from, $to, $treePosition);

    public function delete(ChainInterface $chain);

    public function exists($from, $to);
}
