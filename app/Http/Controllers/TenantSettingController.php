<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Ramsey\Uuid\Uuid;
use App\TenantSetting;
use Illuminate\Support\Str;

class TenantSettingController extends Controller
{
    protected $validations;

    public const LANGUAGES = 'languages';
    public const LANGUAGE_DEFAULT = 'language_default';

    public function __construct()
    {
        $this->validations = TenantSetting::getValidations();
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        return TenantSetting::getKeysForNamespace($request->input('namespace'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        return TenantSetting::create(
            [
            'key'   => $request->input('key'),
            'value' => $request->input(),
            ]
        );
    }

    protected static function getSetting($id)
    {
        if (Uuid::isValid($id)) {
            return TenantSetting::find($id);
        } else {
            return TenantSetting::where('key', $id)->first();
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        return self::getSetting($id);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int                      $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
        $setting = self::getSetting($id);

        if ($setting == null) {
            if (!Uuid::isValid($id)) {
                $setting = TenantSetting::create(
                    [
                    'key' => $id,
                    'value' => $request->input()
                    ]
                );
            } else {
                //TODO: throw proper exception
                die('not good!');
            }
        }

        $setting->value = $request->input();
        $setting->save();
    }

    public function validateForNamespace(Request $request, $validations, $namespace = null)
    {
        if ($namespace == null) {
            return $this->validate($request, $validations);
        }

        $validations = collect($validations)->mapWithKeys(
            function ($value, $key) use ($namespace) {
                return [Str::after($key, $namespace . ':') => $value];
            }
        )->all();

        $data = $this->validate($request, $validations);

        return collect($data)->mapWithKeys(
            function ($value, $key) use ($namespace) {
                return [$namespace . ':' . $key => $value];
            }
        )->all();
    }


    public function updateMany(Request $request)
    {
        $data = $this->validateForNamespace($request, $this->validations, $request->input('namespace'));

        $settings = TenantSetting::whereIn('key', array_keys($data))->get();

        foreach ($settings as $setting) {
            $setting->value = $data[$setting->key];
            $setting->save();

            unset($data[$setting->key]);
        }

        foreach ($data as $key => $value) {
            $setting = new TenantSetting();
            $setting->key = $key;
            $setting->value = $value;
            $setting->save();
        }

        //Return all settings
        return $this->index($request);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $setting = self::getSetting($id);
        $setting->destroy();
    }

    public function uiSettings()
    {
        return response(
            TenantSetting::where('key', 'like', 'ui:%')->get()->mapWithKeys(
                function ($item) {
                    return [ Str::after($item->key, 'ui:') => $item->value ];
                }
            )
        )->header('Cache-Control', 'public, max-age=31536000');
    }
}
