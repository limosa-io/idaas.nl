<?php

/**
 * An authentication chain is a connected set of authentication modules, structued in a directed graph.
 */

namespace App\AuthChain\Repository;

use App\AuthChain\Object\UserDummy;
use App\AuthChain\Object\Eloquent\SubjectInterface;

class UserRepository implements UserRepositoryInterface
{
    public function createForSubject(SubjectInterface $subject)
    {
        $user = new UserDummy();

        $user->setUsername($subject->getIdentifier());

        return $user;
    }

    /**
     * Should get implemented by an implementer
     */
    public function findForSubject(SubjectInterface $subject)
    {
        $user = new UserDummy();
        $user->setId($subject->getIdentifier());

        return $user;
    }

    public function findByIdentifier(?string $identifier)
    {
        return null;
    }
}
