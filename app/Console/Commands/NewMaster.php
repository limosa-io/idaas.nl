<?php

/**
 * TODO: Add new "OpenIDConnect" module and connect to other tenant OR self tenant.
 */

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Role;
use App\OAuthScope;
use App\OpenIDProvider;
use App\Tenant;
use App\AuthLevel;
use App\Http\Controllers\OpenIDKeyController;
use App\AuthModule;
use App\AuthTypes\OpenIDConnect;
use App\Client;
use App\OpenIDKey;
use App\Repository\KeyRepository;
use App\AuthChain\Types\Start;
use App\AuthChain;
use Illuminate\Support\Facades\Hash;
use App\User;
use App\Http\Controllers\MailTemplateController;
use App\AuthChain\Types\Password;

class NewMaster extends NewTenant
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenant:master {subdomain : Subdomain} {admin : Admin}';

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
            self::createTenant(
                $this->argument('subdomain'),
                null,
                true,
                function () use ($username) {
                    return $this->ensureUser($username);
                }
            );
        }
    }
}
