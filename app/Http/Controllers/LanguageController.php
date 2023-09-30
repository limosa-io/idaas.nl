<?php

/**
 * Retrieves translations
 */

namespace App\Http\Controllers;

use App\Tenant;
use App\Translation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Lang;

class LanguageController extends Controller
{
    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public static function defaults(string $locale = null, $fallback = true)
    {
        if ($locale != null && file_exists(resource_path("lang/$locale"))) {
            return [
                'login' => Lang::get('login', [], $locale),
                'general' => Lang::get('general', [], $locale),
            ];
        } else {
            if ($fallback) {
                return self::defaults('en-GB', false);
            } else {
                return [];
            }
        }
    }

    public static function dotted($array)
    {
        //from https://stackoverflow.com/questions/10424335/php-convert-multidimensional-array-to-2d-array-with-dot-notation-keys
        $ritit = new \RecursiveIteratorIterator(new \RecursiveArrayIterator($array));
        $result = [];
        foreach ($ritit as $leafValue) {
            $keys = [];
            foreach (range(0, $ritit->getDepth()) as $depth) {
                $keys[] = $ritit->getSubIterator($depth)->key();
            }
            $result[implode('.', $keys)] = $leafValue;
        }

        return $result;
    }

    public static function getCustomizations(string $locale = null)
    {
        return $locale == null ? [] : Translation::where('locale', $locale)->orderBy('key')->get()->mapWithKeys(
            function ($item) {
                return [$item->key => $item->value];
            }
        );
    }

    public function customizations(string $locale)
    {
        // [object, object, object]
        $result = self::getCustomizations($locale);

        return response(
            $result->toJson(JSON_FORCE_OBJECT),
            200,
            [
                'content-type' => 'application/json',
            ]
        );
    }

    protected static function setArray(&$array, $keys, $value)
    {
        $keys = explode('.', $keys);
        $current = &$array;
        foreach ($keys as $key) {
            $current = &$current[$key];
        }
        $current = $value;
    }

    public static function getArray(string $locale = null)
    {
        $result = self::defaults($locale);

        $customzations = self::getCustomizations($locale);

        foreach ($customzations as $key => $value) {
            self::setArray($result, $key, $value);
        }

        return $result;
    }

    public function get(string $locale)
    {
        return response(self::getArray($locale))->header('Cache-Control', 'public, max-age=31536000');
    }

    public function store(Request $request, string $locale)
    {
        $allowed = array_keys(self::dotted($this->defaults($locale)));
        $input = $request->input();

        $translations = Translation::where(['locale' => $locale])->get();

        $filtered = self::dotted(
            array_filter(
                $input,
                function ($key) use ($allowed) {
                    return in_array($key, $allowed);
                },
                ARRAY_FILTER_USE_KEY
            )
        );

        foreach ($filtered as $key => $value) {
            Translation::updateOrCreate(
                [
                    'key' => $key,
                    'locale' => $locale,
                ],
                [
                    'value' => $value,
                ]
            );
        }

        foreach ($translations as $translation) {
            if (! in_array($translation->key, array_keys($filtered))) {
                $translation->delete();
            }
        }

        $tenant = resolve(Tenant::class);
        $tenant->updateVersion();
        $tenant->save();
    }
}
