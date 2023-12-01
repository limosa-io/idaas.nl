<?php

/**
 * Allows managing tenants
 */

namespace App\Http\Controllers;

use App\Console\Commands\NewTenant;
use App\Role;
use App\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TenantController extends Controller
{
    protected $validations;

    public function __construct()
    {
        $this->validations = [
            'subdomain' => [
                'required',
                'regex:/^[a-z]+-?[a-z]+?$/',
                'min:3',
                'not_in:www,www1,www2,www3,www4,idaas,mail,ns1,ns2,ns3,ftp,static,cdn',
            ],
        ];

        $this->middleware('can:control,App\Tenant');
    }

    public function getMyTenants()
    {
        /** @var \App\Subject */
        $user = Auth::user();

        $tenant_ids = Role::withoutGlobalScopes()->whereIn('id', $user->getRoles()->toArray())->pluck('tenant_id');

        return Tenant::withoutGlobalScopes()->whereIn('id', $tenant_ids);
    }

    public function index()
    {
        //TODO: Should be possible to simplify this
        return $this->getMyTenants()->withCount('clients')->withCount('users')->get();
    }

    public function destroy($id)
    {
        /** @var \App\AuthChain\Subject */
        $subject = Auth::user();
        if (Role::whereIn('id', $subject->getRoles())->pluck('tenant_id')->contains($id)) {
            return Tenant::destroy($id);
        }

        return response(null, 404);
    }

    public function store(Request $request)
    {
        $validations = $this->validations;
        $validations['subdomain'][] = 'unique:tenants,subdomain';

        $data = $this->validate($request, $validations);

        $tenant = NewTenant::createTenant(
            $data['subdomain'],
            $request->user()->getUser()
        );

        return null;
    }
}
