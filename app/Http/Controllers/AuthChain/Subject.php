<?php

/**
 * Extends the living subject (not the eloquent subject)
 */

namespace App\Http\Controllers\AuthChain;

use App\Subject as AppSubject;
use Laravel\Passport\Token;

class Subject extends \App\AuthChain\Object\Subject
{
    public function getApprovedScopes(?string $appId)
    {
        if ($appId === null) {
            return [];
        }

        // $this->getIdentifier()
        $scopes = Token::whereIn(
            'user_id',
            AppSubject::where(
                'identifier',
                $this->getIdentifier()
            )->get(['id'])->pluck('id')->all()
        )->where(
            ['client_id' => $appId]
        )->get()->pluck('scopes')->flatten()->unique()->all();

        return $scopes;
    }
}
