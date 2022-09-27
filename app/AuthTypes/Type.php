<?php

/**
 * A module is of a certain type
 */

namespace App\AuthTypes;

use App\AuthChain\Module\ModuleInterface;
use App\AuthChain\Module\ModuleResult;
use App\AuthChain\State;
use App\AuthChain\Subject;
use Illuminate\Http\Request;

interface Type
{
    /**
     * Run before execution
     *
     * @return self
     */
    public function init(Request $request, State $state, ModuleInterface $module);

    /**
     * Execute. Returns
     *
     * @return App\AuthChain\Module\ModuleResult
     */
    public function process(Request $request, State $state, ModuleInterface $module);

    /**
     * @return string
     */
    public static function getIdentifier();

    public function getDefaultName();

    public function getConfigValidation();
    public function getPublicConfigKeys();


    public function getDefaultGroup();

    public function isPassive();

    public function isEnabled(?Subject $subject);

    public function remembered();

    public function canAutoRedirect();

    /**
     * @return ModuleResult
     */
    public function getRedirectResponse(Request $request, State $state, ModuleInterface $module);

    public function shouldCreateUser(ModuleInterface $module);

    public function getInfo();
}
