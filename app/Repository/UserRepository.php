<?php

namespace App\Repository;

use ArieTimmerman\Laravel\AuthChain\Repository\ChainRepository as BaseChainRepository;
use App\AuthChain;
use ArieTimmerman\Laravel\AuthChain\Module\ChainInterface;
use App\Subject;
use ArieTimmerman\Laravel\AuthChain\Repository\UserRepositoryInterface;
use App\User;
use ArieTimmerman\Laravel\AuthChain\Object\Eloquent\SubjectInterface;
use App\Http\Controllers\SCIMMeController;
use ArieTimmerman\Laravel\SCIMServer\Http\Controllers\ResourceController;
use App\CloudFunctionHelper;
use App\CloudFunction;
use ArieTimmerman\Laravel\SCIMServer\ResourceType;

class UserRepository implements UserRepositoryInterface
{

    public function createForSubject(SubjectInterface $subject)
    {

        $user = null;

        if (config('serverless.openwhisk_enabled')) {
            $cloudFunction = CloudFunction::where('type', 'jit')->first();

            if ($cloudFunction != null) {
                $cloudResult = CloudFunctionHelper::invoke(
                    $cloudFunction, [
                    'subject'=>$subject,
                    'context' => [

                    ]
                    ]
                );

                if (isset($cloudResult['user']) && is_array($cloudResult['user'])) {
                    $result = $cloudResult['user'];

                    if(!isset($result['schemas'])) {
                        $result['schemas'] = ['urn:ietf:params:scim:schemas:core:2.0:User','arietimmerman:ice'];
                    }

                    $user = ResourceController::createFromSCIM(ResourceType::user(), $result, null, null, true);

                }
            }
        }
        
        if ($user == null) {
            $user = User::create(
                [
                'name' => $subject->getEmail(),
                'email' => $subject->getEmail(),
                'password' => null,
                ]
            );
        
            $user->name = $subject->getIdentifier();
        }

        return $user;

    }

    public function findForSubject(SubjectInterface $subject)
    {
        return User::where('email', strtolower($subject->getEmail()))->first();
    }

    public function findByIdentifier(?string $identifier)
    {

        return User::where(['email'=>strtolower($identifier)])->orWhere(['name'=>strtolower($identifier)])->first();

    }

}

