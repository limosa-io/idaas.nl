<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\TenantSetting;

class Webhook implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;

    protected $model;
    protected $action;

    protected $settings = null;

    public static function getWebHookUrl()
    {
        $webhook = TenantSetting::where('key', 'webhook:webhook_url')->first();

        return $webhook ? $webhook->value : null;
    }

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(array $model, $action)
    {
        $this->model = $model;
        $this->action = $action;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {   

        $guzzle = new \GuzzleHttp\Client(['verify' => false, 'connect_timeout'=> 1.0, 'read_timeout' => 1.0, 'headers' => ['Content-Type' => 'application/json']]);
        
        //TODO: if it fails, log it for the tenant user...
        $guzzle->request(
            'POST', self::getWebHookUrl(), [
            \GuzzleHttp\RequestOptions::JSON => [
                'action' => strtolower(substr($this->action, strrpos($this->action, '\\') + 1)),
                'model' => json_encode($this->model)
            ]
            ]
        );
    }
}
