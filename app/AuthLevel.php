<?php

namespace App;

use App\Model;
use Illuminate\Contracts\Auth\Authenticatable;
use App\AuthChain\AuthLevelInterface;
use App\Scopes\TenantTrait;

class AuthLevel extends Model implements AuthLevelInterface
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'authchain_levels';

    protected $hidden = ['provider_id', 'type', 'tenant_id'];

    /**
     * Returns a string reporesentation of the level
     */
    public function getLevel()
    {
        return $this->level;
    }

    public function setLevel($level)
    {
        $this->level = $level;

        return $this;
    }

    /**
     * Returns a string representation of the type
     */
    public function getType()
    {
        return $this->type;
    }

    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Convers a json object to the AuthLevel
     */
    public static function fromJsonObject($json)
    {
        $result = [];

        if ($json == null) {
        } elseif (is_array($json)) {
            foreach ($json as $k) {
                //TODO: This null check is a workaround. Fix it.
                if ($k == null) {
                    continue;
                }
                $result[] = self::where(['level' => $k->level,'type' => $k->type])->first();
            }
        } else {
            $result[] = self::where(['level' => $json->level,'type' => $json->type])->first();
        }

        return count($result) == 0 ? null : $result;
    }

    public function jsonSerialize(): array
    {
        return [
            'id'    => $this->id,
            'type' => $this->type,
            'level' => $this->level,
        ];
    }

    public function equals(?AuthLevelInterface $authLevel)
    {
        if ($authLevel == null) {
            return false;
        }

        return $this->getType() == $authLevel->getType() && $this->getLevel() == $authLevel->getLevel();
    }

    public function compare(?AuthLevelInterface $authLevel)
    {
        $result = -1;

        if ($this->equals($authLevel)) {
            $result = 0;
        }

        //TODO: implement the rest

        return $result;
    }

    public function getIdentifier()
    {
        return $this->id;
    }

    public function modules()
    {
        return $this->belongsToMany('\App\AuthModule', 'authmodule_authlevel');
    }

    public function provider()
    {
        return $this->belongsTo('App\OpenIDProvider', 'provider_id');
    }

    public function __toString()
    {
        return $this->id;
    }
}
