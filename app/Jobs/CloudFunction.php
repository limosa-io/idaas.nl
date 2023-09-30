<?php

namespace App\Jobs;

use App\CloudFunction as CloudFunctionModel;
use App\CloudFunction\HandlerInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CloudFunction implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

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
        /** @var HandlerInterface */
        $handler = resolve(HandlerInterface::class);
        $handler->invoke(
            CloudFunctionModel::find($this->cloudFunctionId),
            $this->parameters
        );
    }
}
