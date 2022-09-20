<?php

namespace App\Http\Controllers\AuthChain\Manage;

use Illuminate\Http\Request;
use App\Http\Controllers\AuthChain\Controller;
use App\Repository\AuthLevelRepository;

class AuthLevelController extends Controller
{
    protected $validations;

    public function __construct()
    {
        $this->validations = [

            'level' => 'required',
            'type' => 'required',

            //'level' => 'nullable|numeric|min:-100|max:100'

        ];
    }

    /**
     *
     */
    public function index(AuthLevelRepository $repository)
    {
        return $repository->all();
    }

    public function get(AuthLevelRepository $repository, $authLevelId)
    {
        return $repository->get($authLevelId);
    }

    public function delete(AuthLevelRepository $repository, $authLevelId)
    {
        //TODO: check if module exists!
        $repository->delete($repository->get($authLevelId));

        //TODO: Delete chains related to the authModuleId!
    }


    public function create(AuthLevelRepository $repository, Request $request)
    {
        $data = $this->validate($request, $this->validations);

        $authLevel = $repository->add($data['level'], $data['type']);

        return $this->update($repository, $request, $authLevel->getIdentifier());
    }

    public function update(AuthLevelRepository $repository, Request $request, $authLevelId)
    {
        $validations = $this->validations;

        $data = $this->validate($request, $validations);

        $authLevel = $repository->get($authLevelId);
        ;

        //var_dump($data);exit;
        $authLevel->setLevel($data['level']);
        $authLevel->setType($data['type']);

        $repository->save($authLevel);

        return $authLevel;
    }
}
