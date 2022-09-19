<?php

namespace App\Repository;

use App\Link;
use App\AuthChain\Module\ModuleInterface;
use App\AuthTypes\Type;
use App\AuthChain\Object\Eloquent\UserInterface;
use App\User;
use App\AuthChain\Object\Eloquent\SubjectInterface;

class LinkRepository
{
    public function getUserById($userId)
    {
        return User::find($userId);
    }

    public function getUser(SubjectInterface $subject, $moduleExact = false)
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

    public function add(Type $type, SubjectInterface $subject, UserInterface $user, ?ModuleInterface $module = null)
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
