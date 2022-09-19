<?php

namespace App\AuthChain\Module;

use Illuminate\Http\Request;
use App\AuthTypes\Type;

class ModuleResultList implements \JsonSerializable, \ArrayAccess
{
    /**
     * @var ModuleResult[]
     */
    private $moduleResults = [];

    public function __construct(array $moduleResults = null)
    {
        $this->moduleResults = $moduleResults ?? [];
    }

    public function offsetSet($offset, $value): void
    {
        if (is_null($offset)) {
            $this->moduleResults[] = $value;
        } else {
            $this->moduleResults[$offset] = $value;
        }
    }

    public function offsetExists($offset): bool
    {
        return isset($this->moduleResults[$offset]);
    }

    public function offsetUnset($offset): void
    {
        unset($this->moduleResults[$offset]);
    }

    public function offsetGet($offset)
    {
        return isset($this->moduleResults[$offset]) ? $this->moduleResults[$offset] : null;
    }

    /**
     * @return self
     */
    public static function fromJson($json)
    {
        if (is_string($json)) {
            $json = json_decode($json);
        }

        $result = new self();

        if ($json) {
            foreach ($json as $moduleJson) {
                if (($e = ModuleResult::fromJson((object) $moduleJson)) != null) {
                    $result[] = $e;
                }
            }
        }

        return $result;
    }

    public function jsonSerialize(): array
    {
        return $this->moduleResults;
    }

    public function __toString()
    {
        return json_encode(self::jsonSerialize());
    }

    /**
     * @return ModuleResult[]
     */
    public function toArray()
    {
        return $this->moduleResults ?? [];
    }

    /**
     * @return ModuleResult
     */
    public function getLast()
    {
        return \end($this->moduleResults) ?: null;
    }

    /**
     * @return ModuleResultList
     */
    public function getRememberAlways()
    {
        $result = new ModuleResultList();

        foreach ($this->moduleResults as $moduleResult) {
            if ($moduleResult->isCompleted() && $moduleResult->rememberAlways) {
                $result[] = $moduleResult;
            }
        }

        return $result;
    }

    /**
     * @return ModuleResultList
     */
    public function getRememberForSession()
    {
        $result = new ModuleResultList();

        foreach ($this->moduleResults as $moduleResult) {
            if ($moduleResult->isCompleted() && !$moduleResult->rememberAlways && $moduleResult->rememberForSession) {
                $result[] = $moduleResult;
            }
        }

        return $result;
    }

    /**
     * Saves the module resuls in the list. Removes existing result with same module id.
     */
    public function overwrite(ModuleResult $result)
    {
        foreach ($this->moduleResults as $key => $r) {
            if ($r->getModule()->getIdentifier() == $result->getModule()->getIdentifier()) {
                unset($this->moduleResults[$key]);
            }
        }

        $this->moduleResults[] = $result;
    }

    public function addAll(ModuleResultList $add, $removeOldestDuplicate = true)
    {
        foreach ($add->toArray() as $moduleResult) {
            $ignore = false;

            foreach ($this->moduleResults as $k => $r) {
                if ($r->getModule()->getIdentifier() == $moduleResult->getModule()->getIdentifier()) {
                    if ($r->getAuthenticationTime() < $moduleResult->getAuthenticationTime()) {
                        unset($this->moduleResults[$k]);
                    } else {
                        $ignore = true;
                    }
                }
            }

            if (!$ignore) {
                $this->moduleResults[] = $moduleResult;
            }
        }

        return $this;
    }

    public function hasPrompted()
    {
        $result = false;

        foreach ($this->moduleResults as $moduleResult) {
            /* @var $moduleResult ModuleResult */
            if ($moduleResult->getPrompted()) {
                $result = true;
                break;
            }
        }

        return $result;
    }
}
