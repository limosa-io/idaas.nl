<?php

/**
 * A Subject is .... This is the chain subject
 *
 * It has an identifier, a type identifier, a set of attributes, and optionally a user id.
 */

namespace App\AuthChain;

use App\AuthChain\ModuleInterface;
use App\AuthChain\ModuleResultList;
use App\AuthChain\Object\App;
use App\AuthChain\Object\Eloquent\SubjectInterface;
use App\AuthTypes\Type;
use App\Repository\LinkRepository;
use App\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class Subject implements \JsonSerializable
{
    public const ATTRIBUTE_EMAIL = 'email';
    public const ATTRIBUTE_MOBILE = 'mobile';
    public const ATTRIBUTE_ROLES = 'roles';
    public const ATTRIBUTE_ACTIVE = 'active';

    // OpenID's locale is the same as SCIM's preferredLanguage
    public const ATTRIBUTE_PREFERRED_LANGUAGE = 'locale';

    protected $userId;

    /**
     * For example: database, facebook, etc.
     *
     * @var string
     */
    protected $typeIdentifier;

    /**
     * TODO: consider creating an arrayable object Attributes
     *
     * @var array
     */
    protected $attributes;
    private $identifier;

    protected $uuid;

    public function __construct($identifier = null)
    {
        $this->identifier = $identifier;
        $this->uuid = (string) Str::orderedUuid();
    }

    public static function with(string $identifier, Type $type, ?ModuleInterface $module = null)
    {
        $subject = new static($type->getIdentifier() . '|' . $identifier);
        $subject->setTypeIdentifier($type->getIdentifier());

        /**
         * @var LinkRepository
        */
        $linkRepository = resolve(LinkRepository::class);

        // Type $type, Subject $subject, ?ModuleInterface $module
        $user = $linkRepository->getUser($subject);

        if ($user != null) {
            // allow create??
            $subject->setUserId($user->id);
        }

        return $subject;
    }

    public static function fromModuleResults(?ModuleResultList $moduleResultList)
    {
        if ($moduleResultList == null) {
            return null;
        }

        $hasSubject = false;

        /**
         * @var App\AuthChain\ModuleResult
         */
        $firstSubject = null;

        $attributes = [];

        foreach ($moduleResultList->toArray() as $moduleResult) {
            if ($moduleResult->getSubject() != null) {
                $hasSubject = true;

                if ($firstSubject == null) {
                    $firstSubject = $moduleResult->getSubject();
                    $attributesNew = $moduleResult
                        ->getSubject()
                        ->getAttributes();

                    $attributes = $attributes + ($attributesNew ?? []);
                }
            }
        }

        if (!$hasSubject) {
            return null;
        }

        $result = new static($firstSubject->getIdentifier());
        $result->setUserId($firstSubject->getUserId());
        $result->setUuid($firstSubject->getUuid());

        $result->setTypeIdentifier((string)$firstSubject->getTypeIdentifier());

        $result->setAttributes($attributes);


        return $result;
    }

    public static function fromJson($json)
    {
        if ($json == null) {
            return null;
        }

        if (!is_object($json)) {
            $json = (object) $json;
        }

        return (new static($json->identifier))
            ->setUuid($json->uuid)
            ->setIdentifier($json->identifier)
            ->setUserId($json->user_id ?? null)
            ->setAttributes((array) $json->attributes)->setTypeIdentifier($json->type);
    }

    /**
     * Get the value of identifier
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * Workaround: method is from eloquent models
     */
    public function getKey()
    {
        return $this->getIdentifier();
    }

    /**
     * Set the value of identifier
     *
     * @return self
     */
    public function setIdentifier($identifier)
    {
        $this->identifier = $identifier;

        return $this;
    }

    /**
     * Get the value of attributes
     *
     * @return array
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    public function getAttribute($key)
    {
        return isset($this->attributes[$key]) ? $this->attributes[$key] : null;
    }

    public function getAttributeAllowUser($key)
    {
        $result = $this->attributes[$key] ?? (($user = $this->getUser()) != null ? $user->getAttribute($key) : null);
        Log::debug('Try to load "' . $key . '" for user: ' . ($user ? $user->getAttribute($key) : null));
        return $result;
    }

    /**
     * Set the value of attributes
     *
     * @param array $attributes
     *
     * @return self
     */
    public function setAttributes(?array $attributes)
    {
        $this->attributes = $attributes ?? [];

        return $this;
    }

    /**
     * @return self
     */
    public function setTypeIdentifier(string $type)
    {
        $this->typeIdentifier = $type;

        return $this;
    }

    /**
     * @return Type
     */
    public function getTypeIdentifier()
    {
        return $this->typeIdentifier;
    }

    public function jsonSerialize(): array
    {
        return [
            'uuid' => $this->uuid,
            'identifier' => $this->getIdentifier(),
            'user_id'    => $this->getUserId(),
            'attributes' => $this->getAttributes(),
            'type'     => $this->getTypeIdentifier(),
        ];
    }

    public function getUserId()
    {
        return $this->userId;
    }

    public function getUuid()
    {
        return $this->uuid;
    }

    public function getUser(): User|null
    {
        return $this->userId ? resolve(LinkRepository::class)->getUserById($this->userId) : null;
    }

    /**
     * @return self
     */
    public function setUserId(?string $userId)
    {
        $this->userId = $userId;

        return $this;
    }

    public function getEmail()
    {
        return $this->getAttribute(self::ATTRIBUTE_EMAIL) ?? (($user = $this->getUser()) != null ? $user->email : null);
    }

    public function getPreferredLanguage()
    {
        return $this->getAttribute(self::ATTRIBUTE_PREFERRED_LANGUAGE) ??
            (($user = $this->getUser()) != null ? $user->preferredLanguage : null);
    }

    public function getMobile()
    {
        return $this->getAttribute(self::ATTRIBUTE_MOBILE);
    }

    public function isActive()
    {
        return $this->getAttribute(self::ATTRIBUTE_ACTIVE)
            ?? (($user = $this->getUser()) != null ? $user->active : false);
    }

    public function getRoles()
    {
        return $this->getAttribute(self::ATTRIBUTE_ROLES);
    }

    public function toUserInfo()
    {
        return [
            'sub' => $this->getIdentifier(),
        ];
    }

    /**
     *
     */
    public function getApprovedScopes(?string $appId)
    {
        return [];
    }


    /**
     * Set the value of uuid
     *
     * @return self
     */
    public function setUuid($uuid)
    {
        $this->uuid = $uuid;

        return $this;
    }

    public function hash()
    {
        $json = $this->jsonSerialize();
        unset($json['uuid']);

        return hash('sha256', json_encode($json));
    }
}
