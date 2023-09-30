<?php

/**
 * TODO: Add new "OpenIDConnect" module and connect to other tenant OR self tenant.
 */

namespace App\Console\Commands;

use Illuminate\Console\Command;

class NewMaster extends NewTenant
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenant:master {subdomain : Subdomain} {admin : Admin} {password?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new master';

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
        if ($this->validateInput()) {
            $username = $this->argument('admin');
            $password = $this->argument('password');
            self::createTenant(
                $this->argument('subdomain'),
                null,
                true,
                function () use ($username, $password) {
                    return $this->ensureUser($username, $password);
                }
            );
        }
    }
}
