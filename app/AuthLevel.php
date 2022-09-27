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

    public const TYPE_OIDC = 'oidc';
    public const TYPE_SAML = 'saml';

    /**
     * Returns a string reporesentation of the level
     */
    public function getLevel(): string
    {
        return $this->level;
    }

    public function setLevel($level): self
    {
        $this->level = $level;

        return $this;
    }

    /**
     * Returns a string representation of the type
     */
    public function getType(): string
    {
        return $this->type;
    }

    public function setType($type): self
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Convers a json object to the AuthLevel
     */
    public static function fromJsonObject($json): array|null
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

    public function equals(?AuthLevelInterface $authLevel): bool
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
        return $this->type . ':' . $this->value;
    }

    public function modules()
    {
        return $this->belongsToMany(AuthModule::class, 'authmodule_authlevel');
    }

    public function provider()
    {
        return $this->belongsTo(OpenIDProvider::class, 'provider_id');
    }

    public function __toString()
    {
        return $this->id;
    }

    public static function oidc($level)
    {
        if ($level == null || (is_array($level) && count($level) == 0)) {
            return null;
        }

        return new self([
            'type' => self::TYPE_OIDC,
            'level' => $level
        ]);
    }

    public static function oidcAll(?array $level)
    {
        if ($level == null || count($level) == 0) {
            return null;
        }

        $result = [];

        foreach ($level as $l) {
            if (($r = self::oidc($l)) != null) {
                $result[] = $r;
            }
        }

        return $result;
    }

    public static function samlAll(?array $level)
    {
        if ($level == null || count($level) == 0) {
            return null;
        }

        $result = [];

        foreach ($level as $l) {
            if (($r = self::saml($l)) != null) {
                $result[] = $r;
            }
        }

        return $result;
    }

    public static function saml($level)
    {
        return new self([
            'type' => self::TYPE_SAML,
            'level' => $level
        ]);
    }
}
