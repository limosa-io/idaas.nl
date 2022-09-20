<?php

/**
 * This module verifies the passwords against the internal user store
 *
 * Example request
 *
 * {
 *     "username": "piet",
 *     "password": "pass123",
 *  "remember": true  // (remember on browser close)
 * }
 */

namespace App\AuthTypes;

use App\AuthChain\Module\ModuleResult;
use Illuminate\Http\Request;
use App\AuthChain\State;
use App\AuthChain\Module\ModuleInterface;
use App\AuthChain\Object\Subject;
use App\Repository\UserRepository;

class Password extends AbstractType
{
    protected $remembered = false;

    /**
     * @return self
     */
    public function init(Request $request, State $state, ModuleInterface $module)
    {
        $this->remembered = $state->getRememberedModuleResult($module) != null;
        return $this;
    }

    public function getDefaultName()
    {
        return "Password log in";
    }

    public function isEnabled(?Subject $subject)
    {
        return $subject == null || $subject->getUserId() != null;
    }

    public function remembered()
    {
        return $this->remembered;
    }

    /**
     * @return ModuleResult
     */
    public function process(Request $request, State $state, ModuleInterface $module)
    {
        $username = $request->input('username');
        $password = $request->input('password');

        $remember = $request->input('remember') === true;

        if (($remembered = $state->getRememberedModuleResult($module)) != null) {
            return $remembered->setPrompted(false);
        } elseif (
            ($user = resolve(UserRepository::class)->findByIdentifier($username)) != null &&
            app('hash')->check($password, $user->password)
        ) {
            $r = $module->baseResult()->setSubject(
                $this->createSubject($username, $this, $module)->setUserId($user->id)
            )->complete()->setPrompted(true);

            if (!$remember) {
                $r->setRememberAlways(false);
                $r->setRememberForSession(false);
            }

            return $r;
        } else {
            return $module->baseResult()->setResponse(
                response(
                    [
                    'error' => 'Username or password incorrect',
                    'module' => $module->getIdentifier(),
                    'remembered' => ($state->getRememberedModuleResult($module) != null)
                    ]
                )->setStatusCode(422)
            );
        }
    }
}
