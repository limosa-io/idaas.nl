<?php

namespace App\AuthChain\Repository;

use App\AuthChain\Module\ChainInterface;
use App\AuthChain\Module\ModuleInterface;
use App\AuthChain\Types\Type;
use App\AuthChain\Object\Eloquent\UserInterface;
use App\AuthChain\Object\Eloquent\SubjectInterface;

interface LinkRepositoryInterface
{
    public function getLinkClass();

    public function getUser(SubjectInterface $subject, $moduleExact = false);

    public function getUserById($userId);

    public function add(Type $type, SubjectInterface $subject, UserInterface $user, ?ModuleInterface $module = null);
}
