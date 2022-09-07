<?php

namespace App\AuthChain\Object;

use App\AuthChain\Object\Eloquent\UserInterface;

class UserDummy implements UserInterface
{
    protected $id;
    protected $username;

    public function __construct()
    {
        $this->id = rand(1, 99999);
        $this->username = 'user' . $this->getId();
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getUsername()
    {
        return $this->username;
    }

    public function setUsername($username)
    {
        $this->username = $username;
    }

    public function getAttributes()
    {
        return [

        ];
    }
}
