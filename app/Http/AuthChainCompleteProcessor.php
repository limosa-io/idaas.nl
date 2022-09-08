<?php

namespace App\Http;

use App\AuthChain\Http\CompleteProcessorInterface;
use Illuminate\Http\Request;
use App\AuthChain\Helper;
use App\AuthChain\State;
use League\OAuth2\Server\AuthorizationServer;
use GuzzleHttp\Psr7\Response as Psr7Response;
use Illuminate\Contracts\Auth\Authenticatable;
use App\Exceptions\NoSessionException;
use League\OAuth2\Server\RequestTypes\AuthorizationRequest;
use App\SAML\Subject as SAMLSubject;
use Exception;
use Idaas\OpenID\RequestTypes\AuthenticationRequest;
use Idaas\OpenID\SessionInformation;
use Idaas\Passport\Http\Controllers\AuthorizationController;
use Laravel\Passport\Bridge\User;
use Laravel\Passport\Http\Controllers\ConvertsPsrResponses;
use App\Subject;

class AuthChainCompleteProcessor implements CompleteProcessorInterface
{
    use ConvertsPsrResponses;

    protected $server;

    public function __construct(AuthorizationServer $server)
    {
        $this->server = $server;
    }

    /**
     * Called when the authchain is finished
     */
    public function onFinish(Request $request, State $state, Authenticatable $subject)
    {
        $authRequest = $state->data;
        Helper::deleteState($state);

        if ($authRequest == null) {
            //TODO: implement a better handler
            throw new NoSessionException('No session');
        } elseif ($state->getScopesApproved() == $state->requestedScopes) {
            if ($authRequest instanceof AuthenticationRequest && $subject instanceof Subject) {
                $r = [];
                foreach ($state->getLevels() as $l) {
                    $r[] = $l->getLevel();
                }

                $authRequest->setSessionInformation(
                    (new SessionInformation())->setAcr(
                        $r
                    )->setAzp($authRequest->getClient()->getIdentifier())
                );

                $authRequest->setUser(new User($subject->getKey()));
                $authRequest->setAuthorizationApproved(true);

                return $this->convertResponse(
                    $this->server->completeAuthorizationRequest($authRequest, new Psr7Response())
                );
            } else {
                return \ArieTimmerman\Laravel\SAML\Http\Controllers\SAMLController::getIdpProcessor(
                    $request,
                    $authRequest
                )->continueSingleSignOn(
                    new SAMLSubject($subject)
                );
            }
        } else {
            if ($authRequest instanceof AuthorizationRequest) {
                return resolve(AuthorizationController::class)->returnError($authRequest);
            } else {
                throw new Exception('Not implemented for SAML');
            }
        }
    }

    public function onCancel(Request $request, ?State $state)
    {
        return redirect($state->onCancelUrl);
    }
}
