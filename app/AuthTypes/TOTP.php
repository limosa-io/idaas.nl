<?php

namespace App\AuthTypes;

use App\AuthChain\Module\ModuleInterface;
use App\AuthChain\State;
use App\AuthChain\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use OTPHP\TOTP as BaseTOTP;
use ParagonIE\ConstantTime\Base32;

class TOTP extends AbstractType
{
    /**
     * For the Admin
     */
    public function getConfigValidation()
    {
        return [

        ];
    }

    public function getDefaultName()
    {
        return "One-Time Password";
    }

    /**
     * Module specific
     */
    public function getInfo()
    {
        return [

        ];
    }

    public function isEnabled(?Subject $subject)
    {
        /**
         * If we cannot determine if the user has enabled this module, assume it is.
         */
        if ($subject == null) {
            return true;
        }

        $result = $subject != null && $subject->getAttributeAllowUser('otp_secret') != null;

        Log::debug('TOTP enabled?: ' . ($result ? 'enabled' : 'disabled'));
        return $result;
    }

    /**
     * Initialize the module
     */
    public function init(Request $request, State $state, ModuleInterface $module)
    {
        $this->remembered = $state->getRememberedModuleResult($module) != null;
    }

    public function remembered()
    {
        return $this->remembered;
    }

    /**
     * Used for generating a TOTP secret
     */
    public static function generateSecret()
    {
        return trim(Base32::encodeUpper(random_bytes(128)), '=');
    }

    /**
     * Process the module
     */
    public function process(Request $request, State $state, ModuleInterface $module)
    {
        $otp = $request->input('otp');

        if (($subject = $state->getSubject()) == null) {
            return $module->baseResult()->setCompleted(false)->setResponse(
                response(
                    [
                    'message' => 'Only usable as second factor'
                    ]
                )
            );
        }

        if (($user = $subject->getUser()) == null) {
            return $module->baseResult()->setCompleted(false)->setResponse(
                response(
                    [
                    'message' => 'Only usable for subjects connected to a user'
                    ]
                )
            );
        }

        if (($secret = $user->getAttribute('otp_secret')) == null) {
            return $module->baseResult()->setCompleted(false)->setResponse(
                response(
                    [
                    'message' => 'You don\'t have configured this module'
                    ]
                )
            );
        }

        $totp = BaseTOTP::create($secret, 30);

        //TODO: remove this
        if ($totp->verify($otp)) {
            $result = $module->baseResult()->setCompleted(true);

            if ($request->input('remember') !== true) {
                $result->setRememberAlways(false);
                $result->setRememberForSession(false);
            }

            return $result;
        } else {
            return $module->baseResult()->setCompleted(false)->setResponse(
                response(
                    [
                    'error' => 'The provided code is incorrect.'
                    ],
                    422
                )
            );
        }
    }
}
