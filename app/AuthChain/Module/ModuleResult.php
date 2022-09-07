<?php

namespace App\AuthChain\Module;

use App\AuthChain\AuthLevel;
use Symfony\Component\HttpFoundation\Cookie;
use Illuminate\Http\Response;
use App\AuthChain\Repository\AuthLevelRepository;
use App\AuthChain\Object\Eloquent\SubjectInterface;
use App\AuthChain\Object\Subject;

class ModuleResult implements \JsonSerializable
{
    /**
     * @var App\AuthChain\Object\SubjectInterface
     */
    protected $subject;

    /**
     * List of approved scopes
     *
     * @var string[]
     */
    public $scopesApproved;

    protected $authenticationTime;

    /**
     * @var App\AuthChain\Module\Module
     */
    protected $module;

    /**
     * The levels returned from this module
     *
     * @var AuthLevel[]
     */
    protected $levels;

    /**
     * @var bool
     */
    protected $completed = false;

    /**
     * @var bool
     */
    protected $prompted;

    /**
     * @var array
     */
    protected $response;

    /**
     * @var Cookie[]
     */
    public $cookies = [];

    /**
     * If true, sets a persistent cookie
     */
    public $rememberAlways = false;

    /**
     * If true, sets a session cookie
     */
    public $rememberForSession = true;

    /**
     * The amount of seconds the module result should be remembered
     */
    public $rememberLifetime = 3600;

    /**
     * Allows storing the module state
     *
     * @var array
     */
    public $moduleState = [];

    /**
     * Allows storing the messages (useful for step-outs such as for social logins or password resets)
     *
     * @var array
     */
    public $messages = [];

    public function __construct()
    {
        $this->authenticationTime = new \DateTime();
    }

    /**
     * @return self
     */
    public static function withSubject(SubjectInterface $subject)
    {
        return (new self())->setSubject($subject);
    }

    /**
     * Get the value of subject
     *
     * @return \App\AuthChain\Object\Eloquent\SubjectInterface
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * Set the value of subject
     *
     * @return self
     */
    public function setSubject(?SubjectInterface $subject)
    {
        $this->subject = $subject;

        return $this;
    }

    /**
     * Get the value of module
     *
     * @return ModuleInterface
     */
    public function getModule()
    {
        return $this->module;
    }

    /**
     * TODO: Might not be in use anymore. Seems incorrect. Should be getTypeObject()
     */
    public function getType()
    {
        return $this->module->getType();
    }

    /**
     * Set the value of module
     *
     * @return self
     */
    public function setModule(ModuleInterface $module)
    {
        $this->module = $module;

        return $this;
    }

    public function setMessages(?array $messages)
    {
        $this->messages = $messages ?? [];

        return $this;
    }

    public function addMessage(Message $message)
    {
        $this->messages[] = $message;

        return $this;
    }

    public function jsonSerialize(): array
    {
        return [
            'subject'   => $this->subject,
            'scopesApproved'   => $this->scopesApproved,
            'time'      => (string) $this->authenticationTime->format('U'),
            'module'    => $this->module ? $this->module->getIdentifier() : null,
            'moduleState' => $this->moduleState,
            'completed' => $this->completed,
            'rememberAlways' => $this->rememberAlways,
            'rememberForSession' => $this->rememberForSession,
            'rememberLifetime' => $this->rememberLifetime,
            'levels' => $this->levels,
            'messages' => $this->messages,
            'prompted' => $this->prompted
            // 'data' =>  // allow storing custom data
        ];
    }

    public static function fromJson($json)
    {
        $module = $json ?
            resolve('App\AuthChain\Repository\ModuleRepositoryInterface')->get($json->module) :
            null;

        // Ignore the result if the module does not exist anymore
        if ($module == null) {
            //LoG::
            return null;
        }

        $result = $json ? (new self())->setModuleState((array) $json->moduleState)
            ->setSubject($json->subject != null ? Subject::fromJson((object) $json->subject) : null)
            ->setCompleted($json->completed ? true : false)
            ->setAuthenticationTime(
                \DateTime::createFromFormat('U', $json->time)
            )
            ->setModule($module)
            ->setPrompted($json->prompted ?? null)
            ->setRememberAlways($json->rememberAlways ?? null)
            ->setRememberForSession(@$json->rememberForSession)
            ->setRememberLifetime($json->rememberLifetime)
            ->setScopesApproved($json->scopesApproved ?? null)
            ->setLevels(self::authLevelsFromJson(@$json->levels))
            ->setMessages(Message::arrayFromJson(@$json->messages ?? null))
                : null;

        return $result;
    }

    private static function authLevelsFromJson(?array $json = null)
    {
        $result = [];

        $repository = resolve(AuthLevelRepository::class);

        if ($json != null) {
            foreach ($json as $l) {
                array_push($result, ...$repository->fromJson($l));
            }
        }

        return $result;
    }

    /**
     * Get the levels returned from this module
     *
     * @return AuthLevel[]
     */
    public function getLevels()
    {
        return $this->levels;
    }

    /**
     * TODO: Use the method from ModuleTrait
     *
     * @return bool
     */
    public function provides($desiredLevel = null, $comparison = 'exact')
    {
        $result = false;

        //AuthLevel
        if (is_array($desiredLevel)) {
            $result = false;

            foreach ($desiredLevel as $l) {
                if ($result = $this->provides($l, $comparison)) {
                    break;
                }
            }


            if (count($desiredLevel) == 0) {
                $result = true;
            }
        } else {
            $result = $desiredLevel == null;

            if (count($this->levels) > 0) {
                foreach ($this->levels as $level) {
                    $compareResult = $level->compare($desiredLevel);

                    if ($comparison == 'exact' && $compareResult == 0) {
                        $result = true;
                        break;
                    } elseif ($comparison == 'minimum' && $compareResult >= 0) {
                        $result = true;
                        break;
                    }
                }
            }
        }

        return $result;
    }

    /**
     * Set the levels returned from this module
     *
     * @param AuthLevel[] $levels The levels returned from this module
     *
     * @return self
     */
    public function setLevels(?array $levels)
    {
        $this->levels = $levels ?? [];

        return $this;
    }

    /**
     * Get the value of completed
     */
    public function isCompleted()
    {
        return $this->completed;
    }


    /**
     * @return self
     */
    public function complete()
    {
        $this->setCompleted(true);
        $this->setResponse(null);

        return $this;
    }

    /**
     * Set the value of completed
     *
     * @return self
     */
    public function setCompleted($completed)
    {
        $this->completed = $completed;

        return $this;
    }

    /**
     * Get the value of input
     *
     * @return \Illuminate\Http\Response
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * Set the value of input
     *
     * @return self
     */
    public function setResponse(?Response $response)
    {
        $this->response = $response;

        return $this;
    }

    /**
     * Get the value of authenticationTime
     */
    public function getAuthenticationTime()
    {
        return $this->authenticationTime;
    }

    /**
     * Set the value of authenticationTime
     *
     * @return self
     */
    public function setAuthenticationTime(\DateTime $authenticationTime)
    {
        $this->authenticationTime = $authenticationTime;

        return $this;
    }


    /**
     * Get the value of prompted
     *
     * @return bool
     */
    public function getPrompted()
    {
        return $this->prompted;
    }

    /**
     * Set the value of prompted
     *
     * @param bool $prompted
     *
     * @return self
     */
    public function setPrompted(?bool $prompted = false)
    {
        $this->prompted = $prompted;

        return $this;
    }

    /**
     * Set allows storing the module state
     *
     * @param array $moduleState Allows storing the module state
     *
     * @return self
     */
    public function setModuleState(?array $moduleState)
    {
        $this->moduleState = $moduleState ?? [];

        return $this;
    }

    /**
     * Set if true, sets a persistent cookie
     *
     * @return self
     */
    public function setRememberAlways(?bool $rememberAlways = false)
    {
        $this->rememberAlways = $rememberAlways;

        return $this;
    }

    /**
     * Set if true, sets a session cookie
     *
     * @return self
     */
    public function setRememberForSession(?bool $rememberForSession = true)
    {
        $this->rememberForSession = $rememberForSession;

        return $this;
    }

    public function setRememberLifetime($rememberLifetime = null)
    {
        $this->rememberLifetime = $rememberLifetime ?? 3600;

        return $this;
    }


    /**
     * Set list of approved scopes
     *
     * @param string[] $scopesApproved List of approved scopes
     *
     * @return self
     */
    public function setScopesApproved(?array $scopesApproved)
    {
        $this->scopesApproved = $scopesApproved;

        return $this;
    }
}
