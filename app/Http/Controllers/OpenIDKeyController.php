<?php

/**
 * For managing the keys to sign access and id_tokens
 */

namespace App\Http\Controllers;

use App\OpenIDKey;
use App\OpenIDProvider;
use App\Repository\KeyRepository;
use Illuminate\Http\Request;

class OpenIDKeyController extends Controller
{
    protected $validations;

    public function __construct()
    {
        $this->validations = [
            'private_key' => ['required', function ($attribute, $value, $fail) {
                try {
                    if (openssl_pkey_get_private($value) === false) {
                        throw new \Exception('Invalid private key');
                    }
                } catch (\Exception $e) {
                    $fail('The provided data is not a valid private key');
                }
            }],
            'x509' => ['required_without:public_key', function ($attribute, $value, $fail) {
                try {
                    openssl_x509_read($value);
                } catch (\Exception $e) {
                    $fail('The provided data is not a valid x509 certificate');
                }
            }],
            'public_key' => ['required_without:x509', function ($attribute, $value, $fail) {
                try {
                    if (openssl_pkey_get_public($value) === false) {
                        throw new \Exception('Invalid public key');
                    }
                } catch (\Exception $e) {
                    $fail('The provided data is not a valid public key');
                }
            }],
        ];
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        return OpenIDKey::all();
    }

    public function store(Request $request)
    {
        $data = $this->validate($request, $this->validations);

        if (! empty($data['x509'])) {
            $key = str_replace(
                [
                    '-----BEGIN CERTIFICATE-----',
                    '-----END CERTIFICATE-----',
                    "\r",
                    "\n",
                    ' ',
                ],
                '',
                $data['x509']
            );
            $keyForParsing =
                "-----BEGIN CERTIFICATE-----\n".
                chunk_split($key, 64, "\n").
                "-----END CERTIFICATE-----\n";
            $details = openssl_pkey_get_details(openssl_pkey_get_public(openssl_x509_read($keyForParsing)));
            $data['public_key'] = $details['key'];
        }

        $oAuthScope = new OpenIDKey();
        $oAuthScope->public_key = $data['public_key'];
        $oAuthScope->private_key = $data['private_key'];
        $oAuthScope->x509 = $data['x509'] ?? null;
        $oAuthScope->provider_id = OpenIDProvider::first()->id;

        $oAuthScope->save();

        return $oAuthScope;
    }

    public static function generateKey()
    {
        return OpenIDKey::forceCreate(
            KeyRepository::generateNew() + ['provider_id' => OpenIDProvider::first()->id, 'active' => false]
        );
    }

    public function createGenerated(Request $request)
    {
        return self::generateKey();
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\OAuthScope  $oAuthScope
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, OpenIDKey $openidKey)
    {
        $data = $this->validate(
            $request,
            [
                'active' => 'boolean',
            ]
        );

        if ($data['active'] == false && OpenIDKey::where('active', true)->count() == 1) {
            return response(['error' => 'You must have at least one active key pair'], 422);
        }

        $openidKey->active = $data['active'];

        $openidKey->save();

        return $openidKey;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\OAuthScope  $oAuthScope
     * @return \Illuminate\Http\Response
     */
    public function destroy(OpenIDKey $openidKey)
    {
        $openidKey->delete();
    }
}
