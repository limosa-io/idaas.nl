<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Tenant;

class ListTenant extends NewTenant
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenant:list';

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
        
        $headers = ['Subdomain', 'Is Master'];
        $tenants = Tenant::all(['subdomain', 'master'])->toArray();

        return $this->table($headers, $tenants);

    }

}