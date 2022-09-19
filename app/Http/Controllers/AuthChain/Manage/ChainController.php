<?php

namespace App\Http\Controllers\AuthChain\Manage;

use Illuminate\Http\Request;
use App\Http\Controllers\AuthChain\Controller;
use App\AuthChain\Module\Module;
use App\AuthChain\Module\ModuleInterface;
use App\AuthChain\Repository\ChainRepository;
use App\AuthChain\Repository\ChainRepositoryInterface;
use App\AuthChain\AuthChain;
use App\Repository\ModuleRepository;

class ChainController extends Controller
{
    protected $validations;

    protected $validationGraph = null;

    protected static function mustBeModule($attribute, $value, $fail)
    {
        $module = resolve(ModuleRepository::class)->get($value);

        if ($module == null) {
            return $fail($attribute . ' is not a valid module.');
        }
    }

    public function getValidations()
    {
        if ($this->validations != null) {
            return $this->validations;
        }

        $this->validations = [
            'from' => ['required', function ($attribute, $value, $fail) {
                self::mustBeModule($attribute, $value, $fail);
            }],
            'to' => ['required', function ($attribute, $value, $fail) {
                self::mustBeModule($attribute, $value, $fail);
            }, function ($attribute, $value, $fail) {

                // TODO: calculate overlap / cycles
                $overlap = false;

                if ($overlap > 0) {
                    return $fail('Cycles are not allowed!');
                }

                // TODO: should check for typeobject ... ?
                // if (
                //     $from->type != 'start' && ! collect($pre)->contains(
                //         function ($value, $key) {
                //             return $value->type == 'start';
                //         }
                //     )
                // ) {
                //     return $fail('The chain must start with the "Start" module!');
                // }

                if (resolve(ChainRepositoryInterface::class)->exists(request('from'), request('to'))) {
                    return $fail('This link already exists!');
                }
            }]
        ];

        return $this->validations;
    }

    /**
     *
     */
    public function index(ChainRepositoryInterface $repository)
    {
        return $repository->all();
    }

    public function add(ChainRepositoryInterface $repository, Request $request)
    {
        $data = $this->validate($request, $this->getValidations());
        // TODO: calculate position
        $position = 1;
        return $repository->add($data['from'], $data['to'], $position);
    }

    public function get(ChainRepositoryInterface $repository, $chainId)
    {
        return $repository->get($chainId);
    }

    public function delete(ChainRepositoryInterface $repository, $chainId)
    {
        //TODO: Check if exists
        return $repository->delete($repository->get($chainId));
    }
}
