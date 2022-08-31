<?php

/**
 * Handles the creation of new tenants
 */

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Role;
use App\OAuthScope;
use App\OpenIDProvider;
use App\HostedIdentityProvider;
use App\Tenant;
use App\AuthLevel;
use App\AuthModule;
use App\AuthTypes\OpenIDConnect;
use App\Client;
use App\OpenIDKey;
use App\Repository\KeyRepository;
use ArieTimmerman\Laravel\AuthChain\Types\Start;
use App\AuthChain;
use App\AuthTypes\Facebook;
use App\AuthTypes\OtpMail;
use Illuminate\Support\Facades\Hash;
use App\User;
use App\EmailTemplate;
use App\Http\Controllers\MailTemplateController;
use ArieTimmerman\Laravel\AuthChain\Types\Password;
use App\AuthTypes\PasswordForgotten;
use App\AuthTypes\Passwordless;
use App\Scopes\TenantScope;
use Illuminate\Support\Str;

class NewTenant extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenant:new {subdomain} {admin}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new tenant';

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
                false,
                function () use ($username) {
                    return $this->ensureUser($username);
                }
            );
        } else {
            $this->error('Invalid input');
        }
    }

    public function ensureUser($admin)
    {
        $user = User::withOutGlobalScope(TenantScope::class)->where(
            [
            'name' => $admin
            ]
        )->orWhere(
            [
                'email' => $admin
                ]
        )->first();

        if ($user == null) {
            $password = Str::random(10);

            $user = User::create(
                [
                    'email' => $admin,
                    'password' => Hash::make($password)
                ]
            );
            $this->info('Create user ' . $admin . ' with password: ' . $password);
        }

        return $user;
    }

    public function validateInput()
    {
        if (!filter_var($this->argument('admin'), FILTER_VALIDATE_EMAIL) && User::withoutGlobalScope(TenantScope::class)->find($this->argument('admin')) == null) {
            $this->error('Please provide a mail address!');
            return false;
        }

        return true;
    }

    public static function createTenant($subdomain, ?User $user, $master = false, \Closure $createUserFunction = null)
    {
        $tenant = null;

        if ($master && ($tenant = Tenant::where(['master' => $master])->first()) != null) {
        } else {
            $tenant = Tenant::updateOrCreate(
                [
                    'subdomain' => $subdomain,
                    'master' => $master
                ]
            );
        }

        $tenant->do(
            function ($tenant) use ($subdomain, $user, $createUserFunction) {
                $roles = [Role::firstOrCreate(
                    ['display' => 'Administrator', 'system' => true]
                ), Role::firstOrCreate(
                    ['display' => 'Read Only', 'system' => true]
                )];


                if ($user == null) {
                    $user = ($createUserFunction)();
                }

                $user->roles()->sync(collect($roles)->pluck('id'), false);

                $provider = OpenIDProvider::first();

                if ($provider == null) {
                    $provider = (new OpenIDProvider())->forceFill(
                        [
                        'liftime_access_token' => 3600,
                        'liftime_refresh_token' => (3600 * 8),
                        'liftime_id_token' => 3600,
                        'response_types_supported' => ['code', 'token', 'id_token', 'code token', 'id_token token'],
                        ]
                    );

                    $provider->save();
                }

                $hostedIdentityProvider = HostedIdentityProvider::first();

                if ($hostedIdentityProvider == null) {
                    $hostedIdentityProvider = new HostedIdentityProvider();

                    $hostedIdentityProvider->signAuthnrequest = false;

                    $samlKeys = KeyRepository::generateNew();

                    $hostedIdentityProvider->keys = [
                    [
                        'type' => 'X509Certificate',
                        'signing' => true,
                        'encryption' => false,
                        'X509Certificate' => $samlKeys['x509'],
                        'private' => $samlKeys['private_key'],
                    ]
                    ];

                    $hostedIdentityProvider->save();
                }


                collect(
                    [
                    [
                    'name' => 'applications:manage',
                    'description' => 'Create and manage your tenant(s)',
                    'system' => true,
                    'provider_id' => $provider->id,
                    ],
                    [
                    'name' => 'openid',
                    'description' => 'Get your unique identifier',
                    'system' => true,
                    'provider_id' => $provider->id,
                    ],
                    [
                    'name' => 'online_access',
                    'description' => 'Access until you\'re logged out',
                    'system' => true,
                    'provider_id' => $provider->id,
                    ],
                    [
                    'name' => 'users:manage',
                    'description' => 'Manage users',
                    'system' => true,
                    'provider_id' => $provider->id,
                    ],
                    [
                    'name' => 'authentication:manage',
                    'description' => 'Manage the authentication chain',
                    'system' => true,
                    'provider_id' => $provider->id,
                    ],
                    [
                    'name' => 'settings:manage',
                    'description' => 'Manage all generic settings',
                    'system' => true,
                    'provider_id' => $provider->id,
                    ],
                    [
                    'name' => 'roles',
                    'description' => 'Get to know your permissions',
                    'system' => true,
                    'provider_id' => $provider->id,
                    ],
                    [
                    'name' => 'phone',
                    'description' => 'Get your phone number',
                    'system' => false,
                    'provider_id' => $provider->id,
                    ],
                    [
                    'name' => 'address',
                    'description' => 'Get your address number',
                    'system' => false,
                    'provider_id' => $provider->id,
                    ],
                    [
                    'name' => 'email',
                    'description' => 'Get your email address',
                    'system' => false,
                    'provider_id' => $provider->id,
                    ],
                    [
                    'name' => 'profile',
                    'description' => 'Get your profile information',
                    'system' => false,
                    'provider_id' => $provider->id,
                    ]
                    ]
                )->each(
                    function ($item, $key) {
                        OAuthScope::firstOrCreate($item);
                    }
                );

                collect(
                    [
                    [
                    'type' => 'oidc',
                    'level' => 'urn:mace:incommon:iap:bronze',
                    'provider_id' => $provider->id
                    ],
                    [
                    'type' => 'oidc',
                    'level' => 'urn:mace:incommon:iap:silver',
                    'provider_id' => $provider->id
                    ],
                    [
                    'type' => 'oidc',
                    'level' => 'urn:mace:incommon:iap:gold',
                    'provider_id' => $provider->id
                    ],
                    [
                    'type' => 'oidc',
                    'level' => 'manage',
                    'provider_id' => $provider->id
                    ],
                    [
                    'type' => 'oidc',
                    'level' => 'activation',
                    'provider_id' => $provider->id
                    ]

                    ]
                )->each(
                    function ($item, $key) {
                        AuthLevel::firstOrCreate($item);
                    }
                );

                OpenIDKey::firstOrCreate(
                    [
                    'provider_id' => OpenIDProvider::first()->id,
                    'active' => true
                    ],
                    KeyRepository::generateNew()
                );

                // Manager is the tenant's manager client. That is, the client connected to the manager application
                $client = Client::firstOrCreate(
                    [
                    'name' => 'Manager',
                    ],
                    [
                    'secret' => Str::random(40),
                    'redirect_uris' => [route('ice.manage.completelogin')],
                    'post_logout_redirect_uris' => [route('ice.manage.completelogout')],
                    'personal_access_client' => false,
                    'password_client' => false,
                    'revoked' => false,
                    'public' => 'public',
                    'trusted' => true,
                    'grant_types' => ['authorization_code', 'implicit', 'refresh_token', 'client_credentials']

                    ]
                );

                $client->defaultAcrValues()->sync([AuthLevel::where(['level' => 'manage'])->first()->id]);

                $tenant->client_id = $client->client_id;
                $tenant->save();

                //TODO: Register a client in the 'trusted identity repository'.
                $client = new Client();

                $urls = [];
                $redirectUri = route('ice.login.openid');

                Tenant::where(['master' => true])->first()->do(
                    function ($master) use (&$client, $tenant, $roles, &$urls, $redirectUri, $user) {
                        $client = Client::firstOrCreate(
                            [
                            'name' => 'Tenant - ' . $tenant->subdomain,
                            ],
                            [
                            'secret' => Str::random(40),
                            'redirect_uris' => [$redirectUri],
                            'grant_types' => ["authorization_code", "implicit", "refresh_token", "client_credentials"],
                            'personal_access_client' => false,
                            'password_client' => false,
                            'revoked' => false,
                            'trusted' => true
                            ]
                        );

                        $urls = [
                        'oidc.userinfo' => route('oidc.userinfo'),
                        'oauth.authorize' => route('oauth.authorize'),
                        'oauth.token' => route('oauth.token'),
                        ];
                    }
                );

                $from = AuthModule::firstOrCreate(
                    ['type' => (new Start())->getIdentifier()],
                    [
                    'name' => 'Start',
                    'skippable' => true,
                    ]
                );

                $to = [];

                $module = AuthModule::updateOrCreate(
                    ['name' => 'Admin Login'],
                    [
                    'skippable' => true,
                    'type' => (new OpenIDConnect())->getIdentifier(),
                    'group' => (new OpenIDConnect())->getDefaultGroup(),
                    'hide_if_not_requested' => true,
                    'config' => [
                        'client_id' => $client->client_id,
                        'client_secret' => $client->secret,
                        'userinfo_endpoint' => $urls['oidc.userinfo'],
                        'scopes' => 'openid roles',
                        'authorization_endpoint' => $urls['oauth.authorize'],
                        'token_endpoint' => $urls['oauth.token']
                    ]
                    ]
                );

                $levels = [
                AuthLevel::where(['level' => 'manage'])->first()->id
                ];

                //The master tenant authenticates against itself, and therefore should have the level as required by the "oidc authentication module".
                if ($tenant->master) {
                    $levels[] = AuthLevel::where(['level' => 'urn:mace:incommon:iap:bronze'])->first()->id;

                    if (env('FACEBOOK_CLIENT_ID') !== null) {
                        $to[] = AuthModule::firstOrCreate(
                            [
                            'name' => (new Facebook())->getDefaultName()
                            ],
                            [
                                'type' => (new Facebook())->getIdentifier(),
                                'config' => [
                                'client_id' => env('FACEBOOK_CLIENT_ID'),
                                'client_secret' => env('FACEBOOK_CLIENT_SECRET')
                                ]
                            ]
                        );
                    }
                }

                $module->authLevels()->sync($levels, false);

                $to[] = AuthModule::firstOrCreate(
                    ['name' => (new Password())->getDefaultName()],
                    [
                    'skippable' => true,
                    'type' => (new Password())->getIdentifier(),
                    'group' => (new Password())->getDefaultGroup()
                    ]
                );

                if ($tenant->master) {
                    $to[] = AuthModule::firstOrCreate(
                        ['name' => (new Passwordless())->getDefaultName()],
                        [
                        'skippable' => true,
                        'type' => (new Passwordless())->getIdentifier(),
                        'group' => (new Passwordless())->getDefaultGroup()
                        ]
                    );
                } else {
                    $to[] = AuthModule::firstOrCreate(
                        ['name' => (new OtpMail())->getDefaultName()],
                        [
                        'skippable' => true,
                        'type' => (new OtpMail())->getIdentifier(),
                        'group' => (new OtpMail())->getDefaultGroup()
                        ]
                    );
                }

                $to[] = AuthModule::firstOrCreate(
                    ['name' => (new PasswordForgotten())->getDefaultName()],
                    [
                    'skippable' => true,
                    'type' => (new PasswordForgotten())->getIdentifier(),
                    'group' => (new PasswordForgotten())->getDefaultGroup()
                    ]
                );

                $to[] = $module;

                foreach ($to as $t) {
                    AuthChain::firstOrCreate(
                        [
                        'from' => $from->id,
                        'to' => $t->id,
                        'position' => 0
                        ]
                    );
                }

                $emailTemplate = EmailTemplate::firstOrCreate(
                    [
                    'default' => true,
                    'type' => EmailTemplate::TYPE_GENERIC,
                    ],
                    [
                    'name' => 'Base Template',
                    'subject' => 'We need to inform you',
                    'body' => ($body = file_get_contents(resource_path() . '/emails/main.mustache.php')),
                    'body_inlined' => MailTemplateController::inline($body)
                    ]
                );

                EmailTemplate::firstOrCreate(
                    [
                    'default' => true,
                    'type' => EmailTemplate::TYPE_ACTIVATION,
                    'parent_id' => $emailTemplate->id
                    ],
                    [
                    'name' => 'Activation',
                    'subject' => 'Activate your account',
                    'body' => ($body = file_get_contents(resource_path() . '/emails/activate.mustache.php')),
                    'body_inlined' => MailTemplateController::inline($body)
                    ]
                );

                EmailTemplate::firstOrCreate(
                    [
                    'default' => true,
                    'type' => EmailTemplate::TYPE_CHANGE_EMAIL,
                    'parent_id' => $emailTemplate->id
                    ],
                    [
                    'name' => 'Email Change',
                    'subject' => 'Confirm your email change',
                    'body' => ($body = file_get_contents(resource_path() . '/emails/change-email.mustache.php')),
                    'body_inlined' => MailTemplateController::inline($body)
                    ]
                );

                EmailTemplate::firstOrCreate(
                    [
                    'default' => true,
                    'type' => EmailTemplate::TYPE_ONE_TIME_PASSWORD,
                    'parent_id' => $emailTemplate->id
                    ],
                    [
                    'name' => 'One Time Password',
                    'subject' => 'Your log in code',
                    'body' => ($body = file_get_contents(resource_path() . '/emails/one-time-password.mustache.php')),
                    'body_inlined' => MailTemplateController::inline($body)
                    ]
                );

                EmailTemplate::firstOrCreate(
                    [
                    'default' => true,
                    'type' => EmailTemplate::TYPE_PASSWORDLESS,
                    'parent_id' => $emailTemplate->id
                    ],
                    [
                    'name' => 'Log in link',
                    'subject' => 'Your log in link',
                    'body' => ($body = file_get_contents(resource_path() . '/emails/passwordless.mustache.php')),
                    'body_inlined' => MailTemplateController::inline($body)
                    ]
                );

                EmailTemplate::firstOrCreate(
                    [
                    'default' => true,
                    'type' => EmailTemplate::TYPE_FORGOTTEN,
                    'parent_id' => $emailTemplate->id
                    ],
                    [
                    'name' => 'Password Forgotten',
                    'subject' => 'Reset your password',
                    'body' => ($body = file_get_contents(resource_path() . '/emails/password-forgotten.mustache.php')),
                    'body_inlined' => MailTemplateController::inline($body)
                    ]
                );
            }
        );

        return $tenant;
    }
}
