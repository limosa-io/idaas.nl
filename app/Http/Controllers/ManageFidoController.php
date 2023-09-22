<?php

namespace App\Http\Controllers;

use App\FidoKey;
use Illuminate\Http\Request;
use lbuchs\WebAuthn\WebAuthn;

class ManageFidoController extends Controller
{
    protected function getWebAuth(): WebAuthn
    {
        // TODO: this should be based on the actual urls used by my.idaas.nl and login.idaas.nl
        return new WebAuthn('notidaas.nl', 'notidaas.nl');
    }

    public function getCreateArguments(Request $request)
    {
        /* @var $subject \App\Subject */
        $subject = $request->user();

        /* @var $user \App\User */
        $user = $subject->getUser();

        $webAuth = $this->getWebAuth();

        $createArgs = $webAuth->getCreateArgs(
            $user->id,
            $user->id,
            $user->id,
            20,
            false,
            false,
            null
        );

        $challenge = $webAuth->getChallenge();

        return response()->json($createArgs)->header('x-challenge', $challenge->getHex());
    }

    // TODO: dit implementeren in de AuthModule / type
    public function getGetArguments(Request $request)
    {
        /* @var $subject \App\Subject */
        $subject = $request->user();

        /* @var $user \App\User */
        $user = $subject->getUser();

        $webAuth = $this->getWebAuth();

        // TODO: lookup credential ids for user

        $createArgs = $webAuth->getGetArgs(
            [
                'credential-id'
            ],
        );

        $challenge = $webAuth->getChallenge();

        return response()->json($createArgs)->header('x-challenge', $challenge->getHex());
    }

    public function register(Request $request)
    {

        /** @var \App\Subject */
        $subject = $request->user();

        /** @var \App\User */
        $user = $subject->getUser();

        $webAuth = $this->getWebAuth();

        $clientDataJSON = base64_decode($request->input('clientDataJSON'));
        $attestationObject = base64_decode($request->input('attestationObject'));
        $challenge = $request->header('x-challenge');

        // processCreate returns data to be stored for future logins.
        // in this example we store it in the php session.
        // Normaly you have to store the data in a database connected
        // with the user name.
        $data = $webAuth->processCreate(
            $clientDataJSON,
            $attestationObject,
            \lbuchs\WebAuthn\Binary\ByteBuffer::fromHex($challenge),
            false,
            true,
            false
        );

        $user->fidoKeys()->create([
            'credential_id' => base64_encode($data->credentialId),
            'credential_public_key' => $data->credentialPublicKey
        ]);

        return json_encode($data);
    }

    public function listFidoKeys(Request $request)
    {
        /** @var \App\Subject */
        $subject = $request->user();

        /** @var \App\User */
        $user = $subject->getUser();

        return $user->fidoKeys()->get();
    }

    public function delete(Request $request, FidoKey $fidoKey)
    {
        /** @var \App\Subject */
        $subject = $request->user();

        /** @var \App\User */
        $user = $subject->getUser();

        // TODO: use better scoping
        $user->fidoKeys()->get()->find($fidoKey->id)->delete();

        return $fidoKey;
    }
}
