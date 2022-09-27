<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Repository\ChainRepository;
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

                if (resolve(ChainRepository::class)->exists(request('from'), request('to'))) {
                    return $fail('This link already exists!');
                }
            }]
        ];

        return $this->validations;
    }

    /**
     *
     */
    public function index(ChainRepository $repository)
    {
        return $repository->all();
    }

    public function add(ChainRepository $repository, Request $request)
    {
        $data = $this->validate($request, $this->getValidations());
        // TODO: calculate position
        $position = 1;
        return $repository->add($data['from'], $data['to'], $position);
    }

    public function get(ChainRepository $repository, $chainId)
    {
        return $repository->get($chainId);
    }

    public function delete(ChainRepository $repository, $chainId)
    {
        //TODO: Check if exists
        return $repository->delete($repository->get($chainId));
    }
}
