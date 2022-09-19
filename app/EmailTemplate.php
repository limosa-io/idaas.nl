<?php

namespace App;

use App\Model;
use TijsVerkoyen\CssToInlineStyles\CssToInlineStyles;
use App\Http\Controllers\LanguageController;
use Illuminate\Support\Str;

class EmailTemplate extends Model
{
    protected $appends = ['is_parent'];

    protected $hidden = ['is_parent', 'tenant_id'];

    protected $casts = ['default' => 'boolean'];

    public const TYPE_GENERIC = 'generic';
    public const TYPE_ACTIVATION = 'activation';
    public const TYPE_FORGOTTEN = 'forgotten';
    public const TYPE_CHANGE_EMAIL = 'change_email';
    public const TYPE_ONE_TIME_PASSWORD = 'one_time_password';
    public const TYPE_PASSWORDLESS = 'passwordless';

    public function getIsParentAttribute()
    {
        return $this->children()->exists();
    }

    public function parent()
    {
        return $this->belongsTo('App\EmailTemplate', 'parent_id');
    }

    public function children()
    {
        return $this->hasMany('App\EmailTemplate', 'parent_id');
    }

    protected static function getPreferredLanguage($preferredLanguage)
    {
        if ($preferredLanguage == null && ($request = request()) != null) {
            $availableLanguages = TenantSetting::where('key', 'ui:languages')->value('value') ?? [];

            $preferredLanguage = $availableLanguages[0] ?? null;

            $options = collect(explode(',', $request->header('accept-language', '')))->map(
                function ($v) {
                    return trim($v);
                }
            )->sort(
                function ($a, $b) {
                    $aQ = floatval(Str::after($a, ';q=') ?? 1);
                    $bQ = floatval(Str::after($a, ';q=') ?? 1);

                    return $aQ > $bQ ? 1 : -1;
                }
            )->all();

            foreach ($options as $option) {
                if (in_array($option, $availableLanguages)) {
                    $preferredLanguage = $option;
                    break;
                }
            }
        }

        return $preferredLanguage;
    }

    public function renderSubject($data = [], $preferredLanguage = null)
    {
        $preferredLanguage = self::getPreferredLanguage($preferredLanguage);

        $data['preferredLanguage'] = $preferredLanguage;

        $template = $this->subject;

        $translations = LanguageController::dotted(LanguageController::getArray($preferredLanguage));

        $options = [
            'pragmas' => [\Mustache_Engine::PRAGMA_BLOCKS, \Mustache_Engine::PRAGMA_FILTERS],
            'helpers' => [
                't' => function ($text) use ($translations) {
                    return $translations[$text] ?? $text;
                }
            ]
        ];

        $m = new \Mustache_Engine($options);

        return $m->render($template, $data);
    }

    public function render($data = [], $preferredLanguage = null)
    {
        $preferredLanguage = self::getPreferredLanguage($preferredLanguage);

        $data['preferredLanguage'] = $preferredLanguage;

        $template = "{{%FILTERS}}\n" . $this->body;

        $translations = LanguageController::dotted(LanguageController::getArray($preferredLanguage));

        $options = [
            'pragmas' => [\Mustache_Engine::PRAGMA_BLOCKS, \Mustache_Engine::PRAGMA_FILTERS],
            'helpers' => [
                't' => function ($text) use ($translations) {
                    return $translations[$text] ?? $text;
                },
                'json_encode' => function ($array) {
                    return json_encode($array);
                }
            ]
        ];

        if ($this->parent != null) {
            $options['partials'] = [
                'parent' => $this->parent->body
            ];

            $template = '{{<parent}}' . $template . '{{/parent}}';
        }

        $m = new \Mustache_Engine($options);

        foreach ($data as $key => &$value) {
            if (!is_string($value)) {
                $value = json_encode($value);
            }
        }

        $result = $m->render($template, $data);

        $cssToInlineStyles = new CssToInlineStyles();

        $result = $cssToInlineStyles->convert($result);

        return $result;
    }
}
