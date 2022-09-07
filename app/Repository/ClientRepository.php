<?php

namespace App\Repository;

use App\Client;
use Illuminate\Http\Request;
use App\AuthChain\Exceptions\ApiException;
use App\Scopes\TenantScope;
use App\Role;
use App\Group;
use App\Exceptions\NoClientException;
use Idaas\Passport\ClientRepository as IdaasClientRepository;

class ClientRepository extends IdaasClientRepository
{
    protected $clientClass = Client::class;

    protected static $cache = [];

    public function __construct()
    {
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

    public function findForManagement($id)
    {
        return $this->clientClass::with(['defaultAcrValues', 'roles', 'groups'])->find($id);
    }

    public function find($id)
    {
        //TODO: sure this is without TenantScope?? Not insecure?
        // phpcs:ignore Generic.Files.LineLength.TooLong
        $client =  self::$cache[$id] ??  self::$cache[$id] = $this->clientClass::withoutGlobalScope(TenantScope::class)->find($id);

        if ($client == null) {
            throw new NoClientException();
        }

        return $client;
    }

    public function updateWithRequest(Client $client, Request $request)
    {
        if (!($client instanceof Client)) {
            throw new ApiException('No good!');
        }

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
