<?php

/**
 * Ensures a user is registered after loggin in with Facebook
 */

namespace App\AuthTypes;

use App\AuthChain\ModuleInterface;
use App\AuthChain\ModuleResult;
use App\AuthChain\State;
use App\Repository\ConsentRepository;
use Illuminate\Http\Request;

class Consent extends AbstractType
{
    protected $remembered = false;

    protected function getScopesApproved(State $state)
    {
        $subject = $state->getSubject();

        $approved = [];

        // TODO: consider using another attribute (not appid) for SAML service providers
        if ($subject != null && $state->appId != null && \Ramsey\Uuid\Uuid::isValid($state->appId)) {
            $approved = $subject->getApprovedScopes($state->appId);
        }

        $result = ($approved + $state->getScopesApproved());

        return $result;
    }

    public function init(Request $request, State $state, ModuleInterface $module)
    {
        $requested = $state->requestedScopes;
        $approved = $this->getScopesApproved($state);
        $this->remembered = collect($state->requestedScopes)->diff($this->getScopesApproved($state))->isEmpty();
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
        if ($request->input('init')) {
            return $module
                ->baseResult()
                ->setResponse(
                    response(resolve(ConsentRepository::class)->getDescriptions($state->requestedScopes))
                )->setCompleted(false);
        }

        return $module
            ->baseResult()
            ->setScopesApproved($state->requestedScopes)
            ->setRememberAlways(false)
            ->setRememberForSession(false)
            ->complete();
    }
}
