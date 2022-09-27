<?php

/**
 * Used by the authentication chain to allow storing module results.
 *
 * Effectivly, this represents the session store.
 */

namespace App\Http\Controllers\AuthChain;

use App\AuthChain\State;
use App\AuthChain\ModuleResult;
use App\ModuleResult as EloquentModuleResult;
use Illuminate\Support\Facades\Session;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use App\AuthChain\ModuleResultList;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Str;
use Carbon\Carbon;

class RememberStorage
{
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function clearModuleResults(Request $request)
    {
        $sessionId = Session::getId();
        $cookieId = Cookie::get('remember_id');

        EloquentModuleResult::where(
            'session_or_cookie_id',
            $sessionId
        )->orWhere('session_or_cookie_id', $cookieId)->delete();

        Cookie::queue(Cookie::forget('remember_id'));

        session()->flush();
    }

    /**
     * Save the state to something related to either a session_id or a cookie ...
     */
    public function saveModuleResults(Authenticatable $subject, State $state)
    {
        $sessionId = Session::getId();
        $cookieId = Cookie::get('remember_id');

        $request = request();

        if (empty($rememberId)) {
            // Ensure the cookie gets send to the client
            $cookieId = Str::uuid();
            Cookie::queue(
                'remember_id',
                $cookieId,
                config('session.cookie_lifetime'),
                '/',
                $request->getHttpHost(),
                $request->secure(),
                true
            );
        }

        $hasSavedNew = false;

        $inserts = [];

        /**
         * @var ModuleResult $r
        */
        foreach ($state->getModuleResults()->toArray() as $r) {
            // Save if whaterver has prompted. Else it forgets the first factor ...
            // TODO: Check if "getPrompted()" is a better check than "getSubject"
            if ($r->getSubject() != null && ($r->rememberAlways || $r->rememberForSession)) {
                $rememberLifetime = $r->rememberLifetime ?? 3600;

                $expiresAt = null;
                if ($r->rememberAlways) {
                    $expiresAt = Carbon::now()->addSeconds(
                        min($rememberLifetime, config('session.cookie_lifetime'))
                    );
                } else {
                    // Save it for a day. Since session.cookie_lifetime is meant to get updated upon activeness
                    $expiresAt = Carbon::now()->addSeconds(min($rememberLifetime, 24 * 3600));
                }

                $inserts[] = [
                    'session_or_cookie_id' =>
                        $r->rememberForSession ? $sessionId : $cookieId,
                    'session' => $r->rememberForSession,
                    'subject_id' => $subject->id,
                    'user_id' => $state->getSubject()->getUserId(),

                    'module_id' => $r->getModule()->getIdentifier(),

                    'user_agent' => substr(
                        (string) request()->header('User-Agent'),
                        0,
                        500
                    ),

                    'module_result' => json_encode($r),

                    'expires_at' => $expiresAt
                ];

                $hasSavedNew = true;
            }
        }

        if ($hasSavedNew) {
            // TODO: why was this line here? Why delete earlier remembered module results?
            EloquentModuleResult::where(
                'session_or_cookie_id',
                $sessionId
            )->orWhere('session_or_cookie_id', $cookieId)->delete();
        }

        foreach ($inserts as $insert) {
            EloquentModuleResult::create($insert);
        }
    }

    public function getRememberedModuleResults(Request $request)
    {
        $result = new ModuleResultList(
            EloquentModuleResult::select('module_result')->where(
                function ($query) use ($request) {
                    $sessionId = $request->session()->getId();
                    $rememberId = Cookie::get('remember_id');

                    $query->where('session_or_cookie_id', $sessionId);

                    if (!empty($rememberId)) {
                        $query->orWhere('session_or_cookie_id', $rememberId);
                    }
                }
            )->get()->map(
                function ($eloquent) {
                    return ModuleResult::fromJson(json_decode($eloquent->module_result));
                }
            )->all()
        );

        return $result;
    }
}
