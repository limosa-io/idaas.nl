<?php

namespace App\Repository;

use App\AuthChain\Repository\ModuleRepositoryInterface;
use App\AuthModule;
use App\AuthChain\Exceptions\ApiException;
use App\AuthChain\Types\Type;
use App\AuthChain\Module\ModuleInterface;
use App\AuthChain\Module\Module;
use App\AuthChain\Types\Consent;
use App\AuthLevel;
use App\OpenIDProvider;

class ModuleRepository implements ModuleRepositoryInterface
{
    protected $modules;

    public function getModules()
    {
        if ($this->modules == null) {
            $this->modules = AuthModule::with(['fromChain', 'toChain'])->get();
        }

        return $this->modules;
    }

    public function all()
    {
        return $this->getModules();
    }

    /**
     * @return AuthModule
     */
    public function get($id)
    {
        if ($id == 'consent') {
            $consent = Module::withTypeAndConfig(new Consent(), ['id' => 'consent', 'levels' => []]);
            $consent->skippable = false;
            return $consent;
        }

        if ($id == null) {
            return null;
        }

        $result = collect($this->getModules())->first(
            function ($value, $key) use ($id) {
                return $value->id == $id;
            }
        );

        if ($result == null) {
            $result = AuthModule::find($id);
        }

        return $result->withConfig();
    }


    /**
     * @return AuthModule
     */
    public function add($name = null, Type $type = null)
    {
        $first = null;

        if ($name == null) {
            $count = 1;
            $name = $type->getDefaultName();

            do {
                $count++;
                $first = AuthModule::where(['name' => $name])->first();
            } while ($first != null && ($name = sprintf('%s (%d)', $type->getDefaultName(), $count)) != null);
        } else {
            $first = AuthModule::where(['name' => $name])->first();
        }

        if ($first != null) {
            return $first;
        }

        $module = new AuthModule();
        $module->name = $name;
        $module->type = $type->getIdentifier();
        $module->system = false;

        $module->forceFill(['group' => $type->getDefaultGroup()]);
        $module->config = [];

        $module->save();

        $authLevel = AuthLevel::firstOrCreate(
            [
            'provider_id' => OpenIDProvider::first()->id,
            'level' => $type->getIdentifier(),
            'type' => 'oidc'
            ]
        );

        $module->authLevels()->syncWithoutDetaching([$authLevel->id]);

        return $module;
    }

    public function delete(ModuleInterface $module)
    {
        if ($module instanceof AuthModule) {
            if ($module->system) {
                throw new ApiException('This is a system module. Could not delete module');
            }

            $module->delete();
        } else {
            throw new ApiException('Could not delete module');
        }
    }

    public function save(ModuleInterface $module)
    {
        if ($module instanceof AuthModule) {
            if ($module->system) {
                $module->enabled = true;
            }

            $module->save();
        } else {
            throw new ApiException('Could not save module');
        }
    }

    public function info($id)
    {
        return $this->get($id)->getInfo();
    }
}
