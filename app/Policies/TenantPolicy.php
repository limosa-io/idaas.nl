<?php

/**
 * Who can manage the current tenant
 */

namespace App\Policies;

use App\Role;
use App\Subject;
use App\SubjectInterface;
use App\Tenant;
use Illuminate\Support\Facades\Cache;

class TenantPolicy
{
    /**
     * Determine if the given post can be updated by the user.
     *
     * @param  \App\User  $user
     * @param  \App\Post  $post
     * @return bool
     */
    public function manage(SubjectInterface $subject)
    {
        $current = ($tenant = resolve(Tenant::class)) != null ? $tenant->id : '_';

        return Cache::remember(
            'subject:can_manage:'.$subject->id.':'.$current,
            10,
            function () use ($subject) {
                // this relies on the fact that Role is scoped to the current tenant
                // display sql for Role::whereIn('id',$subject->getRoles())
                return Role::whereIn(
                    'id',
                    $subject->getRoles()
                )->exists();
            }
        );
    }

    /**
     * Wether a user is allowed to control (his) tenants: list and create.
     *
     * Only allowed for master by users with a specific group
     */
    public function control(Subject $subject)
    {
        return config(
            'app.tenant_control_group'
        ) == null || (
            ($tenant = resolve(Tenant::class)) != null &&
            $tenant->master &&
            $subject->user->groups()->where('groups.id', config('app.tenant_control_group'))->exists()
        );
    }

    public function manageOther(SubjectInterface $subject)
    {
        return false; //true; //Role::whereIn('id', $subject->getRoles())->exists();
    }
}
