<?php

/**
 * Allows managing tenants
 */

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Tenant;
use App\Console\Commands\NewTenant;
use Illuminate\Support\Facades\Auth;
use App\Role;

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
                'not_in:www,www1,www2,www3,www4,idaas,mail,ns1,ns2,ns3,ftp,static,cdn'
            ]
        ];

        $this->middleware('can:control,App\Tenant');
    }

    public function getMyTenants()
    {
        /** @var \App\AuthChain\Object */
        $user = Auth::user();
        return Tenant::whereIn('id', Role::whereIn('id', $user->getRoles())->pluck('tenant_id'));
    }

    public function index()
    {
        //TODO: Should be possible to simplify this
        return $this->getMyTenants()->withCount('clients')->withCount('users')->get();
    }

    public function destroy($id)
    {
        /** @var \App\AuthChain\Object\Subject */
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

        $tenant = NewTenant::createTenant($data['subdomain'], $request->user()->getUser());

        return null;
    }
}
