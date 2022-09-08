<?php

namespace App\Http\Controllers\AuthChain\Manage;

use Illuminate\Http\Request;
use App\Http\Controllers\AuthChain\Controller;
use App\AuthChain\Repository\ModuleRepository;
use App\AuthChain\Module\Module;
use App\AuthChain\Module\ModuleInterface;
use App\AuthChain\AuthChain;
use App\AuthChain\Repository\ModuleRepositoryInterface;
use Illuminate\Support\Facades\Auth;

class AuthModuleController extends Controller
{
    protected $validations;

    public function __construct()
    {
        $this->validations = [

            'enabled' => 'required|boolean',
            'hide_if_not_requested' => 'required|boolean',
            'skippable' => 'required|boolean',
            'levels' => 'nullable|array',

            'remember' => 'nullable|in:cookie,session',
            'remember_lifetime' => 'nullable|integer|min:0',
            // 'levels.*.level' => ['required'],
            // 'levels.*.type' => ['required'],
            'name'   => 'nullable|min:3',
            'type'   => ['required', function ($attribute, $value, $fail) {
                if (!isset(AuthChain::$typeMap[$value])) {
                    return $fail(
                        $attribute . ' is not a valid type. Allowed are: ' .
                            implode(', ', array_keys(AuthChain::$typeMap))
                    );
                }
            }],
            'config'    => 'nullable|array',

            'group'    => 'nullable|string',

        ];
    }

    /**
     *
     */
    public function index(ModuleRepositoryInterface $repository)
    {
        return $repository->all();
    }

    public function get(ModuleRepositoryInterface $repository, $authModuleId)
    {
        return $repository->get($authModuleId);
    }

    public function info(ModuleRepositoryInterface $repository, $authModuleId)
    {
        return $repository->info($authModuleId);
    }

    public function delete(ModuleRepositoryInterface $repository, $authModuleId)
    {
        //TODO: check if module exists!
        $repository->delete($repository->get($authModuleId));

        //TODO: Delete chains related to the authModuleId!
    }


    public function create(ModuleRepositoryInterface $repository, Request $request)
    {
        $data = $this->validate($request, $this->validations);

        $class = AuthChain::$typeMap[$data['type']];

        $module = $repository->add($data['name'] ?? null, new $class());

        $request->merge(['name' => $module->name]);

        return $this->update($repository, $request, $module->getIdentifier(), true);
    }

    public function update(ModuleRepositoryInterface $repository, Request $request, $authModuleId, $keepLevels = false)
    {
        $validations = $this->validations;

        $data = $this->validate($request, $validations);

        $module = $repository->get($authModuleId);
        ;

        //re-run validations for module specific validations
        $validations = $validations + $module->getTypeObject()->getConfigValidation();
        $data = $this->validate($request, $validations);

        $module->enabled = $data['enabled'];
        $module->skippable = $data['skippable'];
        $module->group = $data['group'] ?? null;

        $module->remember = $data['remember'] ?? null;
        $module->remember_lifetime = $data['remember_lifetime'] ?? null;

        $module->hide_if_not_requested = $data['hide_if_not_requested'];

        if (!$keepLevels) {
            $module->syncLevels(($data['levels'] ?? null)  ? collect($data['levels'])->pluck('id')->toArray() : []);
        }

        $module->name = $data['name'];

        // $module->getTypeObject()->
        $module->config = $data['config'] ?? [];

        $repository->save($module);

        return $module;
    }
}
