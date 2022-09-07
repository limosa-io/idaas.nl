<?php

/**
 * An authentication chain is a connected set of authentication modules, structued in a directed graph.
 */

namespace App\AuthChain\Repository;

use App\AuthChain\Config\Config;
use App\AuthChain\Exceptions\ApiException;
use App\AuthChain\Module\Module;
use App\AuthChain\Module\ModuleInterface;
use App\AuthChain\Types\Type;

class ModuleRepository implements ModuleRepositoryInterface
{
    protected $modules = null;

    protected static $seen = false;

    public function __construct()
    {
        if (self::$seen) {
            debug_print_backtrace();
            exit;
        }
        self::$seen = true;
    }

    public function all()
    {
        if ($this->modules == null) {
            $moduleConfigs = Config::getInstance()->get('authchain.modules');

            $this->modules = [];

            foreach ($moduleConfigs as $config) {
                $this->modules[] = Module::withTypeAndConfig(new $config['type'](), $config)->withConfig();
            }
        }

        return $this->modules;
    }

    /**
     * @return ModuleInterface
     */
    public function get($id)
    {
        $result = null;

        foreach ($this->modules as $module) {
            if ($module->getIdentifier() == $id) {
                $result = $module->withConfig();
            }
        }

        return $result;
    }


    public function info($id)
    {
        $result = null;

        foreach ($this->modules as $module) {
            if ($module->getIdentifier() == $id) {
                $result = $module;
            }
        }

        return $result->getInfo();
    }

    /**
     * @return ModuleInterface
     */
    public function add($name, Type $type)
    {
        throw new ApiException('Operation not supported');
    }

    public function delete(ModuleInterface $module)
    {
        throw new ApiException('Operation not supported');
    }

    public function save(ModuleInterface $module)
    {
        throw new ApiException('Operation not supported');
    }
}
