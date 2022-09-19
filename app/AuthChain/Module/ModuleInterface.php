<?php

namespace App\AuthChain\Module;

use Illuminate\Http\Request;
use App\AuthTypes\Type;
use App\AuthChain\State;
use App\AuthChain\AuthLevel;
use Illuminate\Foundation\Console\PackageDiscoverCommand;
use App\AuthChain\Exceptions\AuthFailedException;

interface ModuleInterface
{
    public function getIdentifier();

    /**
     * @return Type
     */
    public function getTypeObject();

    public function init(Request $request, State $state);

    /**
     * @return AuthLevel[]
     */
    public function getLevels();

    /**
     * @param $levels AuthLevel[]
     */
    public function setLevels(array $levels);

    public function syncLevels(array $levels);

    //optional
    public function remembered();

    //optional
    public function isPassive();

    /**
     * Configuration information
     */
    public function getInfo();

    /**
     * @return ModuleResult
     */
    public function baseResult();

    /**
     * @return ModuleResult
     */
    public function process(Request $request, State $state);

    public function provides($desiredLevel = null, $comparison = 'exact');
}
