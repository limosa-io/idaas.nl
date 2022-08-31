<?php

/**
 * Lists the access tokens in use. For management purposes.
 */

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Laravel\Passport\Token;

class TokenController extends Controller
{
    public function index(Request $request)
    {
        $request->validate(
            [
            'size' => 'nullable|integer|min:1|max:100',
            'query' => 'nullable|min:0|max:100'
            ]
        );

        $query = \App\Token::with(
            ['subject.user' => function ($query) {
                $query->select(['id', 'email', 'name', 'displayName']);
            }, 'client:client_id,name']
        )->where('revoked', false);

        if ($request->input('user_id')) {
            $query = $query->whereHas(
                'subject.user',
                function ($query) use ($request) {
                    $query->where('id', $request->input('user_id'));
                }
            );
        }

        if ($request->input('query')) {
            $query = $query->whereHas(
                'subject.user',
                function ($query) use ($request) {
                    $query->where(
                        'email',
                        'like',
                        '%' . $request->input('query') . '%'
                    )->orWhere(
                        'name',
                        'like',
                        '%' . $request->input('query') . '%'
                    );
                }
            )->orWhereHas(
                'subject',
                function ($query) use ($request) {
                    $query->where(
                        'identifier',
                        'like',
                        '%' . $request->input('query') . '%'
                    );
                }
            );
        }

        return $query->orderBy('created_at', 'desc')->paginate($request->input('size', 20));
    }

    /**
     * Management revocation endpoint
     */
    public function revoke(Request $request)
    {
        Token::findOrFail($request->input('token'))->revoke();

        return response(null, 200);
    }
}
