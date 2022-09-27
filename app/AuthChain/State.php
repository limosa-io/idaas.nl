<?php

/**
 * Stores the authentication state
 */

namespace App\AuthChain;

use App\AuthChain\Module\ModuleInterface;
use App\AuthChain\Module\ModuleResult;
use App\AuthChain\Module\ModuleResultList;
use App\AuthLevel;
use App\Exceptions\ApiException;
use App\Repository\AuthLevelRepository;
use App\Repository\SubjectRepository;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class State implements \JsonSerializable, Jsonable
{
    /**
     * To fight replay-attacks, set a stateId
     */
    protected $stateId;

    /**
     * And set a counter as well
     */
    protected $counter = 1;

    /**
     * @var ModuleResultList
     */
    protected $moduleResults;

    /**
     * @var ModuleResultList
     */
    public $moduleResultsRemembered;

    protected $stateTime;

    /**
     * @var ModuleResult
     */
    protected $incomplete;

    /**
     * @var AuthLevel[]
     */
    protected $requiredAuthLevel;

    /**
     * @var string[]
     */
    public $requestedScopes;

    /**
     *
     * @var string[]
     */
    public $defaultScopes = [];

    /**
     * Which of the requested scopes are required?
     * TODO: implement this
     *
     * @var string[]
     */
    public $essentialScopes;

    /**
     * @var string[]
     */
    public $requestedClaims;

    /**
     * @var string[]
     */
    public $essentialClaims;

    /**
     * @var bool
     */
    protected $prompt = false;

    /**
     * @var bool
     */
    public $display = false;

    /**
     * @var string
     */
    protected $loginHint = null;

    /**
     * When true, no user-interaction is allowed
     *
     * @var bool
     */
    protected $passive = false;

    /**
     * @var string
     */
    public $onFinishUrl = null;

    /**
     * @var string
     */
    public $onCancelUrl = null;

    /**
     * @var string
     */
    public $retryUrl = null;

    /**
     * @var string
     */
    public $policyUrl = null;

    /**
     * @var string
     */
    public $termsUrl = null;

    /**
     * @var string
     */
    public $active = null;

    /**
     * @var string
     */
    public $uiServerLocation = null;

    /**
     * @var bool
     */
    public $done = false;

    /**
     * @var UIServer
     */
    public $uiServer = null;

    public $maxAge = null;

    /**
     * Logo for display
     */
    public $logo = null;

    /**
     * Name for display
     */
    public $name;

    public $appId;

    /**
     * Store aribtrary data
     */
    public $data;

    public function setData($data)
    {
        $this->data = $data;
    }

    /**
     * Only allow creating State objects using static functions
     */
    public function __construct()
    {
        $this->moduleResults = new ModuleResultList();
    }

    /**
     * Here you have access to session data
     *
     * @return self
     */
    public static function fromRequest(Request $request)
    {

        // Create a new State
        $state = new self();
        $state->newSession();

        $state->setModuleResultsRemembered(Session::getRemembered($request));

        return $state;
    }

    /**
     *
     * @return ModuleResult
     */
    public function getRememberedModuleResult(ModuleInterface $module)
    {
        $result = null;

        if (
            $module->provides($this->getRequiredAuthLevel())
            && $this->needsPrompt()
            && !$this->getModuleResults()->hasPrompted()
        ) {
            return null;
        }

        if ($this->moduleResultsRemembered != null) {
            foreach ($this->moduleResultsRemembered->toArray() as $remembered) {
                /* @var Module $remembered  */
                if ($remembered->getModule()->getIdentifier() == $module->getIdentifier()) {
                    $result = $remembered;

                    if (
                        $this->maxAge != null
                        && $result->getAuthenticationTime()->diff(new \DateTime())->s > $this->maxAge
                    ) {
                        $result = null;
                    }

                    break;
                }
            }
        }

        return $result;
    }

    public function completed(ModuleResult $moduleResult)
    {
        $this->moduleResults[] = $moduleResult;
    }

    public function addResult(ModuleResult $moduleResult)
    {
        if ($moduleResult->isCompleted()) {
            $this->completed($moduleResult);
            $this->setIncomplete(null);

            // Since modules can be enabled based on the chosen subject, we need to re-evaluate the init scripts
            resolve(AuthChain::class)->init(request(), $this);
        } else {
            $this->setIncomplete($moduleResult);
        }

        return $this;
    }

    public function newSession(): self
    {

        //TODO: Ensure absolute randomness of this state id. Use a secure random generator
        $this->stateId = (string) Str::orderedUuid();

        $this->counter++;

        return $this;
    }

    /**
     * @return ModuleResult
     */
    public function getLastCompleted()
    {
        return $this->moduleResults->getLast();
    }

    /**
     * @return AuthLevel[]
     */
    public function getLevels()
    {
        $levels = [];

        if ($this->moduleResults != null) {
            foreach ($this->moduleResults->toArray() as $r) {
                foreach ($r->getLevels() as $l) {
                    $levels[] = $l;
                }

                $levels[] = new AuthLevel([
                    'type' => null,
                    'level' => $r->getModule()->getIdentifier()
                ]);
            }
        }

        return $levels;
    }

    /**
     * Checks if this state provides the required level
     */
    public function provides($desiredLevel = null, $comparison = 'exact')
    {
        $result = false;

        // By default, always return null about state completion... unless ...
        if (
            $desiredLevel == null &&
            ($this->getLastCompletedModule() == null || $this->getLastCompletedModule()->getIdentifier() != 'consent')
        ) {
            return $result;
        }

        if ($this->moduleResults != null) {
            foreach ($this->moduleResults->toArray() as $r) {
                $result = $r->provides($desiredLevel, $comparison);

                if ($result) {
                    break;
                }
            }
        }

        return $result;
    }

    /**
     * @return ModuleInterface
     */
    public function getLastCompletedModule()
    {
        return ($result = $this->getLastCompleted()) ? $result->getModule() : null;
    }

    /**
     * @return State
     */
    public static function fromCode(Request $request, $code)
    {
        if ($code == null) {
            return null;
        }

        $json = json_decode($code);

        $result = new self();

        $result->setModuleResults(ModuleResultList::fromJson(@$json->res));
        $result->setModuleResultsRemembered(ModuleResultList::fromJson(@$json->rem));

        $result->setCounter($json->ctr);
        $result->setstateId($json->tkn);
        $result->setData(unserialize($json->data));

        $repository = resolve(AuthLevelRepository::class);

        $result->setRequiredAuthLevel($repository->fromJson($json->lev));

        $result->setIncomplete(ModuleResult::fromJson($json->inc));
        $result->setUiServer(UIServer::fromJson($json->ser));
        $result->setAppId($json->app ?? null);
        $result->setOnFinishUrl($json->oke);
        $result->setOnCancelUrl($json->nok ?? null);
        $result->setRetryUrl($json->ret ?? null);
        $result->setDefaultScopes($json->def ?? []);
        $result->setLoginHint($json->hint ?? null);

        $result->setTermsUrl($json->ter ?? null);
        $result->setPolicyUrl($json->pri ?? null);

        $result->requestedClaims = $json->cla ?? null;
        $result->requestedScopes = $json->sco ?? null;

        $result->logo = $json->log ?? null;
        $result->name = $json->nam ?? null;

        $result->maxAge = $json->max_age ?? null;

        $result->setDisplay($json->display ?? null);

        return $result;
    }

    public function toArray()
    {
        return [
            //private:
            'res' => $this->moduleResults,
            'ctr' => $this->counter, //counter
            'tkn' => $this->stateId, //stateId
            'data' => serialize($this->data),
            'rem' => $this->moduleResultsRemembered,
        ] + $this->toArrayPublic();
    }

    public function toArrayPublic()
    {
        return [
            //public (sign!)
            'lev' => $this->requiredAuthLevel,
            'inc' => $this->incomplete,


            'oke' => $this->onFinishUrl,
            'nok' => $this->onCancelUrl,
            'ret' => $this->retryUrl,
            'api' => route('chainProcessor', ['module' => 'NAME_OF_THE_MODULE']),
            'fin' => route('authchain.complete'),
            'don' => $this->done,

            'sco' => $this->requestedScopes,
            'cla' => $this->requestedClaims,
            'def' => $this->defaultScopes,
            'app' => $this->appId,

            'ser' => $this->uiServer,

            //TODO: introduce url shortener
            'log' => strlen($this->logo) < 300 ? $this->logo : null,
            'nam' => $this->name,

            'hint' => $this->loginHint,

            'ter' => $this->termsUrl,
            'pri' => $this->policyUrl,

            'subject' => $this->getSubject() != null ? 'yes' : null,
            'display' => $this->display,

            'max_age' => $this->maxAge,

            //TODO: remove, should only be private!
            // 'res' => $this->moduleResults,

        ];
    }

    /**
     * Get the value of requiredAuthLevel
     *
     * @return AuthLevel[]
     */
    public function getRequiredAuthLevel()
    {
        return $this->requiredAuthLevel;
    }

    /**
     * Set the value of requiredAuthLevel
     *
     * @param AuthLevel $requiredAuthLevel
     *
     * @return self
     */
    public function setRequiredAuthLevel($requiredAuthLevel = null)
    {
        if ($requiredAuthLevel == null || is_array($requiredAuthLevel)) {
            $this->requiredAuthLevel = $requiredAuthLevel;
        } elseif ($requiredAuthLevel instanceof AuthLevel) {
            $this->requiredAuthLevel = [$requiredAuthLevel];
        } else {
            throw new ApiException('Invalid parameter');
        }

        return $this;
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    //TODO: delete this method
    public function __toString()
    {
        return $this->stateId;
    }

    public function toJson($options = 0)
    {
        return json_encode($this->jsonSerialize(), $options);
    }

    /**
     * Get the value of counter
     */
    public function getCounter()
    {
        return $this->counter;
    }

    /**
     * Set the value of counter
     *
     * @return self
     */
    public function setCounter($counter)
    {
        $this->counter = $counter;

        return $this;
    }

    /**
     * Get the value of stateId
     */
    public function getstateId()
    {
        return $this->stateId;
    }

    /**
     * Set the value of stateId
     *
     * @return self
     */
    public function setstateId($stateId)
    {
        $this->stateId = $stateId;

        return $this;
    }

    /**
     * Get the value of moduleResults
     *
     * @return ModuleResultList
     */
    public function getModuleResults()
    {
        return $this->moduleResults;
    }

    /**
     * Set the value of moduleResults
     *
     * @param ModuleResult[] $moduleResults
     *
     * @return self
     */
    public function setModuleResults(ModuleResultList $moduleResults)
    {
        $this->moduleResults = $moduleResults ?? new ModuleResultList();

        return $this;
    }

    public function setModuleResultsRemembered(ModuleResultList $moduleResultsRemembered)
    {
        $this->moduleResultsRemembered = $moduleResultsRemembered ?? new ModuleResultList();

        return $this;
    }

    /**
     * Get the value of incomplete
     *
     * @return ModuleResult
     */
    public function getIncomplete()
    {
        return $this->incomplete;
    }

    /**
     * Set the value of incomplete
     *
     * @param ModuleResult $incomplete
     *
     * @return self
     */
    public function setIncomplete(?ModuleResult $incomplete)
    {
        $this->incomplete = $incomplete;

        return $this;
    }

    /**
     * Get the value of prompt
     *
     * @return bool
     */
    public function needsPrompt()
    {
        return $this->prompt;
    }

    /**
     * Set the value of prompt
     *
     * @param bool $prompt
     *
     * @return self
     */
    public function setPrompt(bool $prompt)
    {
        $this->prompt = $prompt;

        return $this;
    }

    /**
     * Set the value of prompt
     *
     * @param bool $prompt
     *
     * @return self
     */
    public function setDisplay(string $display = null)
    {
        $this->display = $display;

        return $this;
    }

    /**
     * Set the login hint
     *
     * @param bool $loginHint
     *
     * @return self
     */
    public function setLoginHint(?string $loginHint)
    {
        $this->loginHint = $loginHint;

        return $this;
    }

    public function setMaxAge(?int $maxAge)
    {
        $this->maxAge = $maxAge;

        return $this;
    }

    /**
     * Get the value of passive
     *
     * @return bool
     */
    public function isPassive()
    {
        return $this->passive;
    }

    /**
     * Set the value of passive
     *
     * @param bool $passive
     *
     * @return self
     */
    public function setPassive(bool $passive)
    {
        $this->passive = $passive;

        return $this;
    }

    public function setOnFinishUrl($url)
    {
        $this->onFinishUrl = $url;

        return $this;
    }

    /**
     * Set the value of uiServerLocation
     *
     * @return self
     */
    public function setUiServerLocation($uiServerLocation)
    {
        $this->uiServerLocation = $uiServerLocation;

        return $this;
    }

    /**
     * Set the value of uiServer
     *
     * @param UIServer $uiServer
     *
     * @return self
     */
    public function setUiServer(UIServer $uiServer)
    {
        $this->uiServer = $uiServer;

        return $this;
    }


    public function setAppId(?string $appId)
    {
        $this->appId = $appId;

        return $this;
    }

    /**
     * Set the value of done
     *
     * @param bool $done
     *
     * @return self
     */
    public function setDone(bool $done)
    {
        $this->done = $done;

        return $this;
    }

    public function setName(?string $name)
    {
        $this->name = $name;

        return $this;
    }

    public function setLogo(?string $logo)
    {
        $this->logo = $logo;

        return $this;
    }

    /**
     * Returns the merged subject
     *
     * @return Subject
     */
    public function getSubject()
    {
        // TODO: Cache this?? Is expensive and called a lot
        return resolve(SubjectRepository::class)->fromModuleResults($this->getModuleResults());
    }

    /**
     * Return all scopes approved
     */
    public function getScopesApproved()
    {
        $scopesApproved = [];

        foreach ($this->getModuleResults()->toArray() as $moduleResult) {
            $scopesApproved += $moduleResult->scopesApproved ?? [];
        }

        return $scopesApproved + $this->defaultScopes;
    }

    public function setDefaultScopes(array $scopes)
    {
        $this->defaultScopes = $scopes;

        return $this;
    }

    public function isCompleted()
    {
        return $this->provides($this->getRequiredAuthLevel());
    }

    /**
     * Set the value of requestedScopes
     *
     * @param string[] $requestedScopes
     *
     * @return self
     */
    public function setRequestedScopes(array $requestedScopes)
    {
        $this->requestedScopes = $requestedScopes;

        return $this;
    }

    /**
     * Set the value of requestedClaims
     *
     * @param string[] $requestedClaims
     *
     * @return self
     */
    public function setRequestedClaims(array $requestedClaims)
    {
        $this->requestedClaims = $requestedClaims;

        return $this;
    }

    /**
     * Set the value of onCancelUrl
     *
     * @param string $onCancelUrl
     *
     * @return self
     */
    public function setOnCancelUrl(string $onCancelUrl)
    {
        $this->onCancelUrl = $onCancelUrl;

        return $this;
    }

    public function setRetryUrl(string $retryUrl)
    {
        $this->retryUrl = $retryUrl;

        return $this;
    }

    public function setPolicyUrl(?string $policyUrl)
    {
        $this->policyUrl = $policyUrl;

        return $this;
    }

    public function setTermsUrl(?string $termsUrl)
    {
        $this->termsUrl = $termsUrl;

        return $this;
    }
}
