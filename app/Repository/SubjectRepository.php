<?php

namespace App\Repository;

use App\Scopes\TenantScope;
use App\Http\Controllers\AuthChain\Subject;
use App\Subject as EloquentSubject;

class SubjectRepository extends \ArieTimmerman\Laravel\AuthChain\Repository\SubjectRepository
{
    public function getSubjectClass()
    {
        return Subject::class;
    }

    public function getEloquentSubjectClass()
    {
        return EloquentSubject::class;
    }

    public function get($id)
    {
        return EloquentSubject::withoutGlobalScope(TenantScope::class)->find($id);
    }
}
