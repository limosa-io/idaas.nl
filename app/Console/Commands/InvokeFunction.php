<?php

namespace App\Console\Commands;

use App\CloudFunction;
use App\CloudFunctionHelper;
use App\Tenant;
use Illuminate\Console\Command;

class InvokeFunction extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'function:invoke {subdomain}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Invoke a cloud function';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $tenant = Tenant::where(['subdomain' => $this->argument('subdomain')])->first();

        $tenant->do(
            function () {
                var_dump(CloudFunctionHelper::invoke(CloudFunction::find('8c48d2b9-22be-4bbf-9e7f-5c203f7b4410')));
            }
        );
    }
}
