<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\ModuleResult;

class ModuleResultController extends Controller
{

    public function index(Request $request)
    {

        $request->validate(
            [
            'size' => 'nullable|integer|min:1|max:100',
            'query' => 'nullable|min:0|max:100'
            ]
        );

        $query = ModuleResult::with(
            ['user'=>function ($query) {
                $query->select('id', 'name', 'email');
            },'subject','module']
        );

        if ($request->input('user_id')) {
            $query = $query->whereHas(
                'subject.user', function ($query) use ($request) {
                    $query->where('id', $request->input('user_id'));
                }
            );
        }

        if ($request->input('query')) {
            $query = $query->whereHas(
                'user', function ($query) use ($request) {
                    $query->where(
                        'email', 'like', '%' . $request->input('query') . '%'
                    )->orWhere(
                        'name', 'like', '%' . $request->input('query') . '%'
                    );
                }
            )->orWhereHas(
                'subject', function ($query) use ($request) {
                        $query->where(
                            'identifier', 'like', '%' . $request->input('query') . '%'
                        );
                }
            );
        }

        return $query->orderBy('created_at', 'desc')->paginate($request->input('size', 100));
    }

    public function delete(Request $request, $moduleResultId)
    {

        $moduleResult = ModuleResult::findOrFail($moduleResultId);

        $moduleResult->delete();

        return "";

    }

}