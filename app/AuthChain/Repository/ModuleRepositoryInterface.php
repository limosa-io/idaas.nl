<?php

/**
 * An authentication chain is a connected set of authentication modules, structued in a directed graph.
 */

namespace App\AuthChain\Repository;

use App\AuthChain\Module\ModuleInterface;
use App\AuthChain\Types\Type;

interface ModuleRepositoryInterface
{
    public function all();

    /**
     * @return ModuleInterface
     */
    public function get($id);

    public function info($id);

    /**
     * @return ModuleInterface
     */
    public function add($name, Type $type);

    public function delete(ModuleInterface $module);

    public function save(ModuleInterface $module);
}
