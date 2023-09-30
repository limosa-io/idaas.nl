<?php

namespace App\Repository;

use App\AuthLevel;
use App\Exceptions\ApiException;
use App\OpenIDProvider;
use Illuminate\Database\Eloquent\Model;

class AuthLevelRepository
{
    /**
     * @return AuthLevel[]
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
     * @return AuthLevel
     */
    public function get($id)
    {
        return AuthLevel::findOrFail($id);
    }

    /**
     * @return AuthLevel
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
     * @return AuthLevel
     */
    public function save(AuthLevel $authLevel)
    {
        if ($authLevel instanceof Model) {
            $authLevel->save();
        } else {
            throw new ApiException('Operation not supported');
        }
    }

    public function delete(AuthLevel $authLevel)
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
