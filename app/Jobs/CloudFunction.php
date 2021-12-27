<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\TenantSetting;
use App\CloudFunction as CloudFunctionModel;
use App\CloudFunctionHelper;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\EmailTemplate;
use App\Mail\StandardMail;
use Ramsey\Uuid\Uuid;

class CloudFunction implements ShouldQueue
{

    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $timeout = 120;


    protected $cloudFunctionId;
    protected $parameters;

    public function __construct(CloudFunctionModel $cloudFunction, $parameters)
    {
        $this->cloudFunctionId = $cloudFunction->id;
        $this->parameters = $parameters;
       
    }

    public function handle()
    {
        
        $result = CloudFunctionHelper::invoke(CloudFunctionModel::find($this->cloudFunctionId), $this->parameters);

        CloudFunctionHelper::handle($result);
        
    }

}