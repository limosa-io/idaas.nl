<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Tenant;

class DeleteTenant extends NewTenant
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenant:delete {subdomain}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'List all tenants (including the master tenant)';

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
        
        $tenant = Tenant::where(['subdomain'=>$this->argument('subdomain')])->first();

        if($tenant == null) {
            $this->error('This tenant does not exists!');
            return;
        }

        $tenant->delete();

        return $this->line('Succesfully delete the tenant');

    }

}