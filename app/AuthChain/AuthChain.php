<?php

/**
 * An authentication chain is a connected set of authentication modules, structued in a directed graph.
 */

namespace App\AuthChain;

use Gliph\Graph\DirectedAdjacencyList;
use App\AuthChain\Module\Module;
use Illuminate\Http\Request;
use App\AuthChain\Module\ModuleInterface;
use App\AuthChain\Types\Consent;
use Illuminate\Support\Facades\Log;

class AuthChain
{
    protected static $t = null;

    /**
     * @var DirectedAdjacencyList
     */
    protected $directedAdjacencyList;
    protected $modules;

    /**
     * List of supported types
     */
    public static $typeMap = [];

    protected $consentModule = null;

    public function __construct(Request $request, State $state)
    {
        $this->init($request, $state);
    }

    public function getConsentModule()
    {
        if ($this->consentModule == null) {
            $this->consentModule = Module::withTypeAndConfig(new Consent(), ['id' => 'consent']);
            $this->consentModule->skippable = false;
        }

        return $this->consentModule;
    }

    public static function buildGraph()
    {
        $directedAdjacencyList = new DirectedAdjacencyList();

        $modules = [];

        foreach (resolve('App\AuthChain\Repository\ModuleRepositoryInterface')->all() as $module) {
            $modules[$module->getIdentifier()] = $module;
            $directedAdjacencyList->ensureVertex($module);
        }

        $from = [];
        $to = [];

        $chainElements = resolve('App\AuthChain\Repository\ChainRepositoryInterface')->all();

        if (count($chainElements) == 0) {
            throw new \Exception('No authentication chain defined');
        }

        foreach ($chainElements as $c) {
            if (!isset($modules[$c->getFrom()])) {
                Log::error(sprintf('Module "%s" exists in chain but seems to be deleted', $c->getFrom()));
                continue;
            }

            if (!isset($modules[$c->getTo()])) {
                Log::error(sprintf('Module "%s" exists in chain but seems to be deleted', $c->getFrom()));
                continue;
            }


            $directedAdjacencyList->ensureArc($modules[$c->getFrom()], $modules[$c->getTo()]);
            $from[] = $c->getFrom();
            $to[] = $c->getTo();
        }

        return $directedAdjacencyList;
    }

    public function init(Request $request, State $state)
    {
        $directedAdjacencyList = self::buildGraph();

        $modules =  iterator_to_array($directedAdjacencyList->vertices());

        $this->setDirectedAdjacencyList($directedAdjacencyList);

        foreach ($modules as &$module) {
            $module->init($request, $state);
        }

        $this->setModules($modules);

        return true;
    }

    /**
     * Set the value of directedAdjacencyList
     *
     * @return self
     */
    public function setDirectedAdjacencyList(DirectedAdjacencyList $directedAdjacencyList)
    {
        $this->directedAdjacencyList = $directedAdjacencyList;

        return $this;
    }

    /**
     * Get the value of modules
     *
     * @return Module[]
     */
    public function getModules()
    {
        return $this->modules;
    }

    /**
     * Set the value of modules
     *
     * @return self
     */
    public function setModules(array $modules)
    {
        $this->modules = $modules;

        return $this;
    }

    public function getPredecessorsOf(ModuleInterface $module)
    {
        $result = [];

        foreach ($this->directedAdjacencyList->predecessorsOf($module) as $predecessor) {
            $result[] = $predecessor;
        }

        return $result;
    }

    public function getSuccessorsOf(ModuleInterface $module)
    {
        $result = [];

        foreach ($this->directedAdjacencyList->successorsOf($module) as $succesor) {
            $result[] = $succesor;
        }

        return $result;
    }

    /**
     * @return Module[]
     */
    public function getAllSuccessorsOf(ModuleInterface $module = null, $result = [])
    {

        /**
         * The consent module is always the last module!
         */
        if ($module == $this->getConsentModule()) {
            return [];
        }


        if ($module == null) {
            return $this->getModules();
        }

        try {
            foreach ($this->getSuccessorsOf($module) as $s) {
                $result[] = $s;

                $result = $this->getAllSuccessorsOf($s, $result);
            }
        } catch (\Exception $e) {
            //TODO: fix this
        }

        return $result;
    }

    /**
     * Get the intermediate step
     *
     * TODO: Ensure the next steps are actually reachable from the start position
     * TODO: prevent endless looping
     */
    public function getNextSteps(ModuleInterface $from = null, ModuleInterface $to, $passive = false)
    {
        $result = [];

        $predecessors = $this->getPredecessorsOf($to);

        if (count($predecessors) == 0 || ($from != null && in_array($from, $predecessors))) {
            if (!$passive || ($to->isPassive() || $to->remembered())) {
                $result[] = $to;
            }
        }

        foreach ($predecessors as $pre) {
            if ($pre == $from) {
                continue;
            }

            Log::debug(
                sprintf(
                    '%s (%s) has predecessor %s (%s)',
                    $to ? $to->name : 'null',
                    $to ? $to->getIdentifier() : 'null',
                    $pre->name,
                    $pre->getIdentifier()
                )
            );

            $next = $this->getNextSteps($from, $pre, $passive);

            foreach ($next as $n) {
                Log::debug(
                    sprintf(
                        '%s (%s) is a next step to %s (%s)',
                        $n->name,
                        $n->getIdentifier(),
                        $pre->name,
                        $pre->getIdentifier()
                    )
                );
            }

            if (count($next) > 0) {
                \array_push($result, ...$next);
            }
        }

        return $result;
    }

    public static function addType($class)
    {
        self::$typeMap[$class::getIdentifier()] = $class;
    }

    public static function getType(string $type)
    {
        return @self::$typeMap[$type] ?? null;
    }
}
