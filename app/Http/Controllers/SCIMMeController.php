<?php
/**
 * Allow self-registration. Returns `x-scim-proof-of-creation` which can be used for finishing the registration module
 */
namespace App\Http\Controllers;

use App\AuthModule;
use Illuminate\Http\Request;
use App\Setting;
use App\AuthLevel;
use ArieTimmerman\Laravel\SCIMServer\Http\Controllers\MeController;
use ArieTimmerman\Laravel\SCIMServer\PolicyDecisionPoint;
use ArieTimmerman\Laravel\SCIMServer\ResourceType;
use Illuminate\Support\Facades\Crypt;
use function GuzzleHttp\json_encode;
use ArieTimmerman\Laravel\SCIMServer\Helper;
use Illuminate\Support\Facades\Mail;
use App\EmailTemplate;
use App\Mail\StandardMail;

class SCIMMeController extends MeController
{

    public function createMe(Request $request, PolicyDecisionPoint $pdp)
    {

        $resourceType = ResourceType::user();

        $resourceObject = parent::createObject($request, $pdp, $resourceType, true);

        $encrypted = Crypt::encryptString(json_encode(['user_id'=>$resourceObject->id]));

        return Helper::objectToSCIMCreateResponse($resourceObject, $resourceType)->header('x-scim-proof-of-creation', $encrypted);
        
    }

    /**
     * Sends a mail with a token which can be used to upgrade an existing access token with the STSGrant.
     * The token is an encrypted array
     */
    public function updateEmail(Request $request)
    {

        $data = $request->validate(
            [
            'email' => 'required|email|max:200',
            'url' => 'required|url'
            ]
        );

        /* @var $subject \App\Subject */
        $subject = $request->user();

        /* @var $user \App\User */
        $user = $subject->getUser();

        $data['user_id'] = $subject->id;
        // notify user

        Mail::to($data['email'])->send(
            new StandardMail(
                null, [
                'url'=> str_replace('{TOKEN}', encrypt($data), $data['url']),
                'subject' => $subject,
                'user' =>  $subject->getUser(),
                ], EmailTemplate::TYPE_CHANGE_EMAIL, $subject->getSubject()->getPreferredLanguage()
            )
        );

        return response(
            [
            'message' => 'success'
            ]
        );

    }

}