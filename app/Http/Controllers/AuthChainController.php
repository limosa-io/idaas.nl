<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\AuthChain\Module\ModuleInterface;
use App\AuthChain\State;
use App\AuthChain\Exceptions\AuthFailedException;
use App\AuthChain\Helper;
use App\AuthModule;
use Illuminate\Http\Response;

class AuthChainController extends Controller
{
    protected function ensureOriginAllowed(Request $request, State $state)
    {
        $origin = $request->header('Origin');

        //check if $request header $request->header('Origin') is present! and allowed!
        if (empty($origin) || !in_array($origin, $state->uiServer->getOrigins())) {
            throw new AuthFailedException(
                sprintf(
                    'Origin "%s" is not allowed. Expected one of: %s',
                    $origin,
                    implode(', ', $state->uiServer->getOrigins())
                )
            );
        }
    }

    protected function allowCors(Request $request, Response $response)
    {
        $origin = $request->header('Origin');

        return $response
            ->header('Access-Control-Allow-Origin', $origin)
            ->header('Access-Control-Allow-Methods', 'POST, OPTIONS')
            ->header('Access-Control-Allow-Headers', 'Content-Type, X-AuthRequest')
            ->header('Vary', 'Origin')
            ->header('Access-Control-Max-Age', '86400');
    }

    /**
     * Return CORS headers for the pre-flight request
     *
     * @return Response
     */
    public function processOptions(Request $request, ModuleInterface $module, State $state)
    {
        $this->ensureOriginAllowed($request, $state);

        return $this->allowCORS($request, response(null));
    }

    public function redirect(Request $request, ModuleInterface $module, State $state)
    {
        $result = $module->getTypeObject()->getRedirectResponse($request, $state, $module);

        $state->addResult($result);

        Helper::saveState($state);

        return $result->getResponse();
    }

    public function process(Request $request, ModuleInterface $module, State $state)
    {
        if ($request->header('Origin')) {
            $this->ensureOriginAllowed($request, $state);
        }

        // Check if $module is allowed considering the $state
        $allowedModules = Helper::getModulesThatLeadsTo(
            $state,
            $state->getRequiredAuthLevel(),
            'exact',
            $state->getLastCompletedModule()
        );

        if (!$allowedModules->contains($module)) {
            throw new AuthFailedException(
                sprintf(
                    'This module may not follow %s! Please choose one of: %s',
                    $state->getLastCompletedModule() == null ?
                    null :
                    sprintf(
                        '%s (%s)',
                        $state->getLastCompletedModule()->name,
                        $state->getLastCompletedModule()->getIdentifier()
                    ),
                    implode(
                        ', ',
                        array_map(fn(AuthModule $value) => sprintf("%s (%s)", $value->name, $value->getIdentifier()), $allowedModules->modules)
                    )
                )
            );
        }

        $result = $module->process($request, $state);

        $state->addResult($result);

        return $this->allowCORS($request, Helper::getAuthResponse($request, $state));
    }

    public function getAuthResponse(Request $request, State $state)
    {
        $response = Helper::getAuthResponseObject($request, $state);

        Helper::saveState($state);

        return $response;
    }

    public function notFound(Request $request)
    {
        return response(null, 404);
    }

    /**
     * Clients initiates a POST request (with browser redirect) to this URL, having authRequest in the POST body.
     */
    public function complete(
        Request $request
    ) {
        $state = Helper::getStateFromSession($request->input('authRequest'));

        //TODO: not sure if I like this ...
        app()->instance(State::class, $state);

        return Helper::getCompleteResponse($request, $state);
    }
}
