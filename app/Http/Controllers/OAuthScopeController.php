<?php

namespace App\Http\Controllers;

use App\OAuthScope;
use App\OpenIDProvider;
use Idaas\Passport\Bridge\ClaimRepository;
use Illuminate\Http\Request;

class OAuthScopeController extends Controller
{
    protected $validations = [
        'name' => 'required|alpha_dash|min:2|unique:o_auth_scopes,name',
        'description' => 'required',
    ];

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        return OAuthScope::all();
    }

    public function mapping()
    {
        return resolve(ClaimRepository::class)->getScopeClaims();
    }

    public function store(Request $request)
    {
        $data = $this->validate($request, $this->validations);

        $oAuthScope = new OAuthScope();
        $oAuthScope->description = $request->input('description');
        $oAuthScope->name = $request->input('name');
        $oAuthScope->provider_id = OpenIDProvider::first()->id;

        $oAuthScope->save();

        return $oAuthScope;
    }

    /**
     * Update the specified resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, OAuthScope $oAuthScope)
    {
        if ($oAuthScope->system) {
            return response(
                [
                    'error' => 'You cannot update system scopes',
                ],
                401
            );
        }

        $validationsCopy = $this->validations;
        $validationsCopy['name'] = $validationsCopy['name'].','.$oAuthScope->id;

        $data = $this->validate($request, $validationsCopy);

        $oAuthScope->forceFill(
            [
                'description' => $request->input('description'),
                'name' => $request->input('name'),
            ]
        );

        $oAuthScope->save();

        return $oAuthScope;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy(OAuthScope $oAuthScope)
    {
        if ($oAuthScope->system) {
            return response(
                [
                    'error' => 'You cannot delete system scopes',
                ],
                401
            );
        }

        $oAuthScope->delete();
    }
}
