<?php

// TODO: implement
namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\CloudFunction as CloudFunctionModel;
use App\CloudFunction\HandlerInterface;
use App\CloudFunctionHelper;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class CloudFunctionDeploy implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public $tries = 3;
    public $timeout = 120;


    protected $cloudFunctionId;

    public function __construct(CloudFunctionModel $cloudFunction)
    {
        // TODO: use dedicated queue that runs jobs in serial
        // $this->onQueue('serial');
        $this->cloudFunctionId = $cloudFunction->id;
    }

    public function handle()
    {
        /** @var HandlerInterface */
        $handler = resolve(HandlerInterface::class);
        $handler->deploy(
            CloudFunctionModel::find($this->cloudFunctionId)
        );
    }
}
