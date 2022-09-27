<?php

namespace App\Repository;

use App\Scopes\TenantScope;
use App\Http\Controllers\AuthChain\Subject;
use App\Subject as EloquentSubject;
use App\AuthChain\Module\ModuleInterface;
use App\AuthTypes\Type;
use App\AuthChain\Module\ModuleResultList;
use App\AuthChain\State;

class SubjectRepository
{

    public function getEloquentSubjectClass()
    {
        return EloquentSubject::class;
    }

    public function get($id)
    {
        return EloquentSubject::withoutGlobalScope(TenantScope::class)->find($id);
    }

    /**
     * Used to save the merged subject
     *
     * @inherited
     */
    public function save(Subject $subject, State $state)
    {

        $eloquentSubject = ($this->getEloquentSubjectClass())::firstOrCreate(
            [
                'id' => $subject->getUuid()
            ],
            [
                'identifier' => $subject->getIdentifier(),
                'user_id' => $subject->getUserId(),
                'subject' => $subject,
                'levels' => collect($state->getLevels())->map(
                    function ($item, $key) {
                        return $item->getLevel();
                    }
                )
            ]
        );

        return $eloquentSubject;
    }

    public function fromModuleResults(?ModuleResultList $moduleResultList)
    {
        return Subject::fromModuleResults($moduleResultList);
    }

    public function with(?string $identifier, Type $type, ?ModuleInterface $module = null)
    {
        if ($identifier == null) {
            return null;
        }

        return Subject::with($identifier, $type, $module);
    }
}
