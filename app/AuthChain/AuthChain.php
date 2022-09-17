<?php

/**
 * An authentication chain is a connected set of authentication modules, structued in a directed graph.
 */

namespace App\AuthChain;

use Gliph\Graph\DirectedAdjacencyList;
use App\AuthChain\Module\Module;
use Illuminate\Http\Request;
use App\AuthChain\Module\ModuleInterface;
use App\AuthChain\Repository\ChainRepositoryInterface;
use App\AuthChain\Types\Consent;
use App\Repository\ChainRepository;
use App\Repository\ModuleRepository;
use Illuminate\Support\Facades\Log;

class AuthChain
{
    protected static $t = null;

    protected $modules;
    protected $chain;

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

    public function init(Request $request, State $state)
    {
        $modules = [];
        $this->chain = [];

        foreach (resolve(ModuleRepository::class)->all() as $module) {
            $modules[$module->getIdentifier()] = $module;

            $module->init($request, $state);
        }


        /** @var ChainRepository */
        $chainRepository = resolve(ChainRepositoryInterface::class);
        $chainElements = $chainRepository->all();

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

            $this->chain[] = [
                $modules[$c->getFrom()],
                $modules[$c->getTo()]
            ];

            Log::debug(sprintf('From: %s (%s). To: %s (%s)', $modules[$c->getFrom()]->name, $c->getFrom(), $modules[$c->getTo()]->name, $c->getTo()));
        }

        $this->modules = $modules;


        return true;
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

    /**
     * @return ModuleInterface[]
     */
    public function getPredecessorsOf(ModuleInterface $module): array
    {
        $result = [];

        foreach ($this->chain as $chain_link) {
            $from = $chain_link[0];
            $to = $chain_link[1];

            if ($module->getIdentifier() == $to->getIdentifier()) {
                $result[] = $from;
            }
        }

        return $result;
    }

    public function getSuccessorsOf(ModuleInterface $module)
    {
        $result = [];

        foreach ($this->chain as $chain_link) {
            $from = $chain_link[0];
            $to = $chain_link[1];

            if ($module->getIdentifier() == $from->getIdentifier()) {
                $result[] = $to;
            }
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
            // Every module could be the successor
            return $this->getModules();
        }


        try {
            foreach ($this->getSuccessorsOf($module) as $s) {
                $result[] = $s;

                foreach ($this->getAllSuccessorsOf($s) as $s2) {
                    $result[] = $s2;
                }

                // $result = $this->getAllSuccessorsOf($s, $result);
            }
        } catch (\Exception $e) {
            //TODO: fix this
        }

        return $result;
    }

    public function getStartingSteps($passive = false)
    {
        $result = [];

        $starting_points = collect($this->chain)->map(fn($value) => $value[0]);
        $to_points = collect($this->chain)->map(fn($value) => $value[1]);

        foreach ($starting_points as $s) {
            if (!$to_points->contains(fn($value) => $value->getIdentifier() == $s->getIdentifier())) {
                if (!collect($result)->contains(fn($v) => $v->getIdentifier() == $s->getIdentifier())) {
                    $result[] = $s;
                }
            }
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

        if ($from == null) {
            return $this->getStartingSteps($passive);
        }

        // the result is one of the following
        $succesors = $this->getSuccessorsOf($from);

        Log::debug(sprintf("Er zijn %d succesors", count($succesors)));


        foreach ($succesors as $succesor) {
            $allSuccessors = $this->getAllSuccessorsOf($succesor);

            Log::debug(sprintf("Er zijn %d allSuccessors", count($allSuccessors)));

            if (
                collect($allSuccessors)->contains(fn ($value, $key) => $value->getIdentifier() == $to->getIdentifier())
            ) {
                if (!$passive || ($to->isPassive() || $to->remembered())) {
                    $result[] = $succesor;
                }
            }

            if ($succesor->getIdentifier() == $to->getIdentifier()) {
                if (!$passive || ($to->isPassive() || $to->remembered())) {
                    $result[] = $succesor;
                }
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
