<?php

/**
 * The home controller for the admin ui.\App\Repository\AuthCodeRepository
 *
 * Shows the admin page.
 */

namespace App\Http\Controllers\Manage;

use App\Group;
use App\Role;
use Idaas\Passport\ClientController as PassportClientController;
use Illuminate\Http\Request;
use Illuminate\Contracts\Validation\Factory as ValidationFactory;
use Idaas\Passport\Bridge\ClientRepository;

class ClientController extends PassportClientController
{
    public function __construct(
        ClientRepository $clients,
        ValidationFactory $validation
    ) {
        parent::__construct($clients, $validation);

        $this->validations['default_acr_values'] = 'nullable|array';

        $this->validations['roles'] = ['nullable', 'array'];
        $this->validations['roles.*.value'] =
            function ($attribute, $value, $fail) {
                if (Role::find($value) == null) {
                    return $fail($attribute . ' is not a valid role.');
                }
            };

        $this->validations['groups'] = ['nullable', 'array'];
        $this->validations['groups.*.value'] =
            function ($attribute, $value, $fail) {
                if (Group::find($value) == null) {
                    return $fail($attribute . ' is not a valid group.');
                }
            };
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return config('app.env') == 'development' ? view('admin_debug') : view('admin');
    }

    /**
     * Useful to check if the logged in user has management permissions.
     */
    public function ping()
    {
        return response(null, 200);
    }

    public function update(Request $request, $clientId)
    {
        $client =  $this->clients->findForManagement($clientId);

        $validations = $this->validations;

        if ($request->input('client_name') === $client->client_name) {
            unset($validations['client_name']);
        }

        $data = $request->validate($validations);

        $acrValues = $data['default_acr_values'] ?? [];
        unset($data['default_acr_values']);

        $roles = $data['roles'] ?? [];
        unset($data['roles']);

        $groups = $data['groups'] ?? [];
        unset($data['groups']);

        $client->forceFill($data)->save();

        $client->defaultAcrValues()->sync(collect($acrValues)->pluck('id')->all());

        $client->roles()->sync(collect($roles)->pluck('value')->all());

        $groups = collect($groups)->pluck('value')->all();
        $client->groups()->sync($groups);

        return $client;
    }
}
