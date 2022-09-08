<?php

/**
 *
 *
 *
 */

namespace App\AuthChain\Repository;

use App\AuthChain\Module\ModuleInterface;
use App\AuthChain\Types\Type;
use App\AuthChain\Object\Eloquent\Subject as EloquentSubject;
use App\AuthChain\Object\Subject;
use App\AuthChain\Object\Eloquent\SubjectInterface;
use App\AuthChain\Module\ModuleResultList;
use App\AuthChain\State;

class SubjectRepository implements SubjectRepositoryInterface
{
    public function getSubjectClass()
    {
        return Subject::class;
    }

    public function getEloquentSubjectClass()
    {
        return EloquentSubject::class;
    }

    /**
     * @inherited
     */
    public function get($id)
    {
        return EloquentSubject::find($id);
    }

    /**
     * Used to save the merged subject
     *
     * @inherited
     */
    public function save(SubjectInterface $subject, State $state)
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
        return $this->getSubjectClass()::fromModuleResults($moduleResultList);
    }

    public function with(?string $identifier, Type $type, ?ModuleInterface $module = null)
    {
        if ($identifier == null) {
            return null;
        }

        return Subject::with($identifier, $type, $module);
    }
}
