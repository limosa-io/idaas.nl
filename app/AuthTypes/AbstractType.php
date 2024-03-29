<?php

namespace App\AuthTypes;

use App\AuthChain\ModuleInterface;
use App\AuthChain\State;
use App\AuthChain\Subject;
use App\Repository\SubjectRepository;
use Illuminate\Http\Request;

abstract class AbstractType implements Type
{
    protected $data;

    public function init(Request $request, State $state, ModuleInterface $module)
    {
        // do nothing
    }

    public function getInfo()
    {
        return [];
    }

    public function getConfigValidation()
    {
        return [
            // config => required
            // 'config.*.client_id
        ];
    }

    public function getPublicConfigKeys()
    {
        return [

        ];
    }

    public function getDefaultGroup()
    {
        return null;
    }

    public function getDefaultName()
    {
        return 'Authentication module';
    }

    public function remembered()
    {
        return false;
    }

    public function isPassive()
    {
        return false;
    }

    public function canAutoRedirect()
    {
        return false;
    }

    public function getRedirectResponse(Request $request, State $state, ModuleInterface $module)
    {
        return null;
    }

    public static function getIdentifier()
    {
        $clazz = substr(get_called_class(), strrpos(get_called_class(), '\\') + 1);

        return strtoupper($clazz) == $clazz ? strtolower($clazz) : lcfirst($clazz);
    }

    public function isEnabled(?Subject $subject)
    {
        return true;
    }

    /**
     * Get the value of data
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Set the value of data
     *
     * @return self
     */
    public function setData($data)
    {
        $this->data = $data;

        return $this;
    }

    /**
     * @return Subject
     */
    public function createSubject(?string $identifier, Type $type, ModuleInterface $module = null)
    {
        return resolve(SubjectRepository::class)->with($identifier, $type, $module);
    }

    public function shouldCreateUser(ModuleInterface $module)
    {
        return false;
    }
}
