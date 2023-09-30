<?php

namespace App\AuthChain;

use App\AuthTypes\Type;
use Illuminate\Http\Request;

interface ModuleInterface
{
    public function getIdentifier();

    /**
     * @return Type
     */
    public function getTypeObject();

    public function init(Request $request, State $state);

    /**
     * @return \App\AuthLevel[]
     */
    public function getLevels();

    /**
     * @param $levels \App\AuthLevel[]
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
