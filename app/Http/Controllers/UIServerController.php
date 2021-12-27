<?php

namespace App\Http\Controllers;

use App\UIServer;
use Illuminate\Http\Request;

class UIServerController extends Controller
{
    protected $validations = [
        'url' => 'required|url',
    ];

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return UIServer::all();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $data = $this->validate($request, $this->validations);

        $uiServer = new UIServer();
        $uiServer->forceFill($data);
        $uiServer->save();

        return $uiServer;
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\UIServer $uIServer
     * @return \Illuminate\Http\Response
     */
    public function show(UIServer $uIServer)
    {
        return $uIServer;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \App\UIServer            $uIServer
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, UIServer $uIServer)
    {
        $data = $this->validate($request, $this->validations);
        $uIServer->forceFill($data);
        $uIServer->save();
        
        return $uIServer;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\UIServer $uIServer
     * @return \Illuminate\Http\Response
     */
    public function destroy(string $uIServer)
    {
        UIServer::destroy($uIServer);

        return response(null, 200);
    }
}
