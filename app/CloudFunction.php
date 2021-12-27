<?php

namespace App;

use App\Model;

class CloudFunction extends Model
{
    public const TYPE_USER_EVENT = "user_event";
    public const TYPE_ATTRIBUTE = "attribute";

    protected $casts = [
        'active' => 'boolean',
        'is_sequence' => 'boolean',
        'variables' => 'array'
    ];

    protected $dates = [
        'created_at',
        'updated_at',
        'run_at'
    ];

    public function getNameAttribute()
    {

        if ($this->is_sequence) {
            return \sprintf('sequence.%s.%s.%s', $this->tenant_id, $this->type, $this->id);
        } else {
            return \sprintf('%s.%s.%s', $this->tenant_id, $this->type, $this->id);
        }
    }

    public function needsDeploy()
    {
        return $this->run_at == null || $this->run_at->lessThan($this->updated_at);
    }

    public function invoke(array $parameters = [])
    {
        return CloudFunctionHelper::invoke($this, $parameters);
    }

    public function getDeployableCode()
    {
        $result = preg_replace(
            '/\/\/ start user code.*?\/\/ end user code/s',
            $this->code,
            file_get_contents(resource_path('serverless/template.js'))
        );

        $result = str_replace('[cloud_function_id]', $this->id, $result);

        return $result;

    }

    protected static function boot()
    {
        parent::boot();

        static::deleting(
            function ($model) {

                TenantSetting::where('key', 'like', 'rule:%')->where('value',  json_encode($model->id))->delete();
            }
        );

        static::deleted(
            function ($model) {

                if (!$model->is_sequence) {

                    if (CloudFunction::where(['type' => $model->type, 'is_sequence' => false])->count() == 0) {
                        CloudFunction::where(['type' => $model->type, 'is_sequence' => true])->delete();
                    } else {
                        $cloudFunction = CloudFunction::firstOrNew(['type' => $model->type, 'is_sequence' => true], ['display_name' => sprintf('sequence_%s', $model->type)]);
                        $cloudFunction->touch();
                    }
                }
            }
        );

        static::saved(
            function ($model) {

                // if we save the cloud function, we can only ensure it gets redeployed if the sequence is also marked as needed for redploy
                if (!$model->is_sequence) {
                    $cloudFunction = CloudFunction::firstOrNew(['type' => $model->type, 'is_sequence' => true], ['display_name' => sprintf('sequence_%s', $model->type)]);

                    $cloudFunction->touch();
                }
            }
        );
    }

    public function getMembers()
    {
        $all = [];

        if ($this->is_sequence) {
            $all = CloudFunction::where('type', $this->type)->where('active', true)->where('is_sequence', false)->orderBy('order')->get();
        }else{
            $all = [$this];
        }

        return $all;
    }

}
