<?php

namespace App;

use Illuminate\Support\Str;

class TenantSetting extends Model
{
    protected $casts = [
        'value' => 'array',
    ];

    public static function getDefaults()
    {
        return [
            'registration:attributes_create' => ['emails'],
            // See App\Listeners\AuthChainSubscriber
            'registration:level_active' => AuthLevel::where('level', 'activation')->first()->id,
        ];
    }

    public static function getValidations()
    {
        return [

            'registration:allow' => 'nullable|boolean',
            'registration:allow_active' => 'nullable|boolean',
            'registration:attributes_create' => ['nullable', 'array', function ($attribute, $value, $fail) {
                //TODO: validate if array contains email attribute
            }],
            'registration:attributes_update' => 'nullable|array',
            'registration:level_active' => 'nullable',

            // UI Settings
            'ui:logo' => 'nullable|url',
            'ui:container_backgroundColor' => 'nullable',
            'ui:container_backgroundImage' => 'nullable|url',
            'ui:button_backgroundColor' => 'nullable',
            'ui:container_positionVertical' => 'nullable',
            'ui:container_positionHorizonal' => 'nullable',
            'ui:navbar_show' => 'nullable',
            'ui:client_logo_show' => 'nullable',
            'ui:client_name_show' => 'nullable',
            'ui:title' => 'nullable',
            'ui:navbar_backgroundColor' => 'nullable',
            'ui:label_display' => 'nullable|in:show,hidden',
            'ui:languages' => ['nullable', 'array', function ($attribute, $value, $fail) {
                if (count($value) !== count(array_unique($value))) {
                    $fail('You have already added this language');
                }
            }],
            'ui:languageDefault' => 'nullable',

            'ui:css' => 'nullable',

            // Webhook
            'webhook:webhook_url' => 'nullable|url',

        ];
    }

    public static function getKeysForNamespace($namespace = null)
    {
        return collect(self::getDefaults())->filter(
            function ($value, $key) use ($namespace) {
                return strpos($key, $namespace.':') === 0;
            }
        )->mapWithKeys(
            function ($item, $key) {
                return [Str::after($key, ':') => $item];
            }
        )->merge(
            self::when(
                $namespace,
                function ($query) use ($namespace) {
                    return $query->where('key', 'like', $namespace.':%');
                }
            )->get()->mapWithKeys(
                function ($item) {
                    // get the key without namespace
                    return [Str::after($item->key, ':') => $item->value];
                }
            )
        );
    }
}
