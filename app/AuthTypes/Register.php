<?php

namespace App\AuthTypes;

use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use App\AuthChain\State;
use App\AuthChain\Module\ModuleResult;
use App\AuthChain\Module\Module;
use App\AuthChain\Module\ModuleInterface;
use App\OpenIDProvider;
use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Parser;
use League\OAuth2\Server\CryptKey;
use Lcobucci\JWT\Signer\Rsa\Sha256;
use Lcobucci\JWT\Signer\Key;
use App\Repository\KeyRepository;
use App\AuthChain\Types\AbstractType;
use App\AuthChain\Helper;
use App\User;
use App\AuthChain\Object\Subject;
use Illuminate\Support\Facades\Crypt;
use App\TenantSetting;

class Register extends AbstractType
{
    public function getConfigValidation()
    {
        return [
            'config.approval' => 'nullable|boolean',
            'config.terms_of_service' => 'nullable|url',
            'config.privacy_policy' => 'nullable|url',

            // use key
        ];
    }

    public function getPublicConfigKeys()
    {
        return [
            'approval',
            'terms_of_service',
            'privacy_policy'
        ];
    }

    public function isPassive()
    {
        return false;
    }

    public function getDefaultGroup()
    {
        return 'register';
    }

    public function getDefaultName()
    {
        return "Registration";
    }

    public function process(Request $request, State $state, ModuleInterface $module)
    {
        //TODO: implement 'init'. Return list of attributes WITH order. Use Vue to display the correct input fields
        if ($request->input('init')) {
            $first = TenantSetting::where('key', 'registration:attributes_create')->first();

            return $module->baseResult()->setCompleted(false)->setResponse(
                response(
                    [
                    'fields' => $first != null ? $first->value : [],
                    'url' => route('scim.me.post')
                    ]
                )
            );
        }

        //TODO: Check if the requester has indeed created this specific user!
        $proof = $request->input('proof-of-creation');
        $proofOfCreation = json_decode(Crypt::decryptString($request->input('proof-of-creation')));

        $user = User::find($proofOfCreation->user_id);

        if ($user == null) {
            return (new ModuleResult())->setCompleted(false)->setResponse(response(['error' => 'User is not found']));
        }

        if (!empty($user->last_successful_login_date)) {
            return (new ModuleResult())
                ->setCompleted(false)
                ->setResponse(response(['error' => 'This user has already logged in']));
        }

        return $module
            ->baseResult()
            ->setCompleted(true)
            ->setSubject((new Subject($user->id))
            ->setUserId($user->id)
            ->setTypeIdentifier('register'));
    }
}
