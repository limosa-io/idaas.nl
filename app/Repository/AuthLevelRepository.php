<?php

namespace App\Repository;

use App\AuthChain\Repository\AuthLevelRepository as BaseAuthLevelRepository;
use App\AuthLevel;
use Illuminate\Database\Eloquent\Model;
use App\AuthChain\Exceptions\ApiException;
use App\AuthChain\AuthLevelInterface;
use App\OpenIDProvider;

class AuthLevelRepository extends BaseAuthLevelRepository
{
    /**
     * @return AuthLevelInterface[]
     */
    public function all()
    {
        return AuthLevel::all();
    }

    public function getByValue($value, $type)
    {
        throw new ApiException('Operation not supported');
    }

    /**
     * @return AuthLevelInterface
     */
    public function get($id)
    {
        return AuthLevel::findOrFail($id);
    }


    /**
     * @return AuthLevelInterface
     */
    public function add($level, $type)
    {
        $authLevel = new AuthLevel();

        $authLevel->provider_id = OpenIDProvider::first()->id;
        $authLevel->level = $level;
        $authLevel->type = $type;
        $authLevel->save();

        return $authLevel;
    }

    /**
     * @return AuthLevelInterface
     */
    public function save(AuthLevelInterface $authLevel)
    {
        if ($authLevel instanceof Model) {
            $authLevel->save();
        } else {
            throw new ApiException('Operation not supported');
        }
    }

    /**
     *
     */
    public function delete(AuthLevelInterface $authLevel)
    {
        if ($authLevel instanceof Model) {
            $authLevel->delete();
        } else {
            throw new ApiException('Operation not supported');
        }
    }

    public function fromJson($json)
    {
        return AuthLevel::fromJsonObject($json);
    }
}
