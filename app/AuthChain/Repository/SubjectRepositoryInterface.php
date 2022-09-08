<?php

namespace App\AuthChain\Repository;

use App\AuthChain\Module\ModuleInterface;
use App\AuthChain\Types\Type;
use App\AuthChain\Module\ModuleResultList;
use App\AuthChain\Object\Eloquent\SubjectInterface;
use App\AuthChain\State;

interface SubjectRepositoryInterface
{
    /**
     * Used to retrieve the subject from the database. Used for user sessions.
     *
     * @return SubjectInterface
     */
    public function get($id);

    /**
     * It's advisable to delete subjects after a period of time
     */
    public function save(SubjectInterface $subject, State $state);

    public function with(?string $identifier, Type $type, ?ModuleInterface $module = null);

    public function fromModuleResults(?ModuleResultList $moduleResultList);
}
