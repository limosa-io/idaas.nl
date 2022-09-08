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

    protected function getValidationGraph($fromId, $toId)
    {
        if ($this->validationGraph == null) {
            $from = resolve(ModuleRepository::class)->get($fromId);
            $to = resolve(ModuleRepository::class)->get($toId);

            $graph = AuthChain::buildGraph();

            $graph->ensureVertex($from);
            $graph->ensureVertex($to);

            $graph->ensureArc($from, $to);

            $this->validationGraph = $graph;
        }

        return $this->validationGraph;
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
                $from = resolve(ModuleRepository::class)->get(request('from'));
                $to = resolve(ModuleRepository::class)->get(request('to'));

                $graph = $this->getValidationGraph(request('from'), request('to'));

                $pre = iterator_to_array($graph->predecessorsOf($from));
                $after = iterator_to_array($graph->successorsOf($from));

                $overlap = count(array_intersect($pre, $after));

                if ($overlap > 0) {
                    return $fail('Cycles are not allowed!');
                }

                // TODO: should check for typeobject ... ?
                if (
                    $from->type != 'start' && ! collect($pre)->contains(
                        function ($value, $key) {
                            return $value->type == 'start';
                        }
                    )
                ) {
                    return $fail('The chain must start with the "Start" module!');
                }

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
        $graph = $this->getValidationGraph(request('from'), request('to'));

        $from = resolve(ModuleRepository::class)->get(request('from'));

        $pre = iterator_to_array($graph->predecessorsOf($from));

        return $repository->add($data['from'], $data['to'], count($pre));
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
