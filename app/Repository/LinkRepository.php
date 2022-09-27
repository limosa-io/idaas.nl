<?php

namespace App\Repository;

use App\AuthChain\Module\ModuleInterface;
use App\AuthChain\Subject;
use App\AuthTypes\Type;
use App\Link;
use App\User;

class LinkRepository
{
    public function getUserById($userId)
    {
        return User::find($userId);
    }

    public function getUser(Subject $subject, $moduleExact = false)
    {
        if ($subject->getUserId() != null) {
            //TODO: Load the user directly. Problem is that the user class is unknown here
            $link = Link::where(['user_id', $subject->getUserId()])->first();

            return $link ? $link->getUser() : null;
        } else {
            $link = Link::where(
                [
                'subject_type' => $subject->getTypeIdentifier(),
                //'subject_module' => $module ? $module->getIdentifier() : null,
                'subject_id' => $subject->getIdentifier()
                ]
            )->first();

            return $link ? $link->getUser() : null;
        }
    }

    public function add(Type $type, Subject $subject, User $user, ?ModuleInterface $module = null)
    {
        $link = Link::create(
            [

            'user_id' => $user->getId(),
            'subject_type' => $type->getIdentifier(),
            'subject_module' => $module ? $module->getIdentifier() : null,
            'subject_id' => $subject->getIdentifier()

            ]
        );

        return $link;
    }
}
