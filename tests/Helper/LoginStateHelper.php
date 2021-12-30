<?php
namespace Tests\Helper;

use Illuminate\Foundation\Testing\TestResponse;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;
use App\AuthTypes\OpenIDConnect;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Response;
use function GuzzleHttp\Psr7\parse_query;
use App\Mail\StandardMail;
use Illuminate\Contracts\Mail\Mailable;
use Illuminate\Support\Str;
use Mockery;

class LoginStateHelper
{

    protected $authRequest;

    /* @var TestResponse */
    public $response;
    public $testCase;
    public $data;

    public function __construct(TestCase $testCase, TestResponse $response = null, $data = [])
    {
        $this->testCase = $testCase;
        $this->response = $response;
        $this->data = $data;
    }

    public function expectInternalOpenIDConnect()
    {
        $this->response->assertStatus(302);
        $this->response->assertHeader('Location');

        // TODO: Check if the request contains scope openid, and state

        // Just follow redirect
        $location = $this->response->baseResponse->headers->get('location');
        $response = $this->testCase->get($location);

        $response->assertStatus(302);
        $response->assertHeader('Location');

        return new static($this->testCase, $response, $this->data);
    }

    public function expectFinishInternalOpenIDConnect()
    {
        $helper = $this->expectFinishClean();

        // Complete OpenID Connect module
        OpenIDConnect::setGuzzleHandler($this->getHandler());
        $response = $helper->testCase->get($helper->response->baseResponse->headers->get('Location'));

        // Expect a return to the completelogin page
        $response->assertStatus(302);
        
        return new static($this->testCase, $response, $this->data);

    }

    protected function getDecoded()
    {
        $decoded = null;
        // The X-AuthRequest header is used when progressing through steps one the same screen
        if($authRequest = $this->response->baseResponse->headers->get('X-AuthRequest')) {
            $decoded = json_decode(base64_decode($authRequest), true);
            // The state-parameter in the location header is used when coming from another page, such as when starting login, or after a step-out
        }else if($this->response->baseResponse->getStatusCode() == 302) {
            $fragment = parse_url($this->response->baseResponse->headers->get('location'), PHP_URL_FRAGMENT);
            parse_str($fragment, $result);

            $this->testCase->assertArrayHasKey('state', $result);

            $response = $this->testCase->get(sprintf('api/authchain/v2/p/authresponse/%s', urlencode($result['state'])));

            $response->assertStatus(200);

            $decoded = $response->json();    
        }
        return $decoded;
    }

    public function expect($type, $data = [])
    {

        $decoded = $this->getDecoded();
                
        $response = null;

        switch($type){
        case 'password':
            $response = self::choosePassword($this->testCase, $decoded, $data);
            break;
        case 'consent':
            $response = self::chooseConsent($this->testCase, $decoded, $data);
            break;
        case 'passwordless':
            $response = self::choosePasswordless($this->testCase, $decoded, $data);
            break;
        case 'facebook':
            $response = self::chooseFacebook($this->testCase, $decoded, $data);
            break;                
        case 'registration':
            $response = self::chooseRegistration($this->testCase, $decoded, $data);
            break;
        default:
            throw Exception('Unknown type');
        }

        return new static($this->testCase, $response, $this->data);
    }

    public function expectFinishClean()
    {
        $decoded = $this->getDecoded();

        $this->testCase->assertArrayHasKey('info', $decoded);
        $this->testCase->assertArrayHasKey('don', $decoded['info']);

        $response = $this->testCase->post(
            $decoded['info']['fin'],
            [
                'authRequest' => $decoded['stateId']
            ],
            [
            ]
        );

        $response->assertStatus(302);
        $response->assertHeader('Location');

        return new static($this->testCase, $response, $this->data);
    }

    public function expectFinish()
    {

        return $this->expectFinishClean();

    }

    public function chooseRegistration(TestCase $testCase, $json, $data)
    {

        $stateId = $json['stateId'];
        $module = collect($json['next'])->first(
            function ($value, $key) {
                return $value['type'] == 'register';
            }
        );

        $testCase->assertNotNull($module);
        
        // First init
        $response = $testCase->post(
            sprintf('/api/authchain/v2/p/%s', urlencode($module['id'])),
            [
                'init' => true
            ],
            [
                'X-StateId' => $stateId
            ]
        );

        $response->assertStatus(200);

        $json = $response->json();
        $testCase->assertArrayHasKey('fields', $json);
        $testCase->assertArrayHasKey('url', $json);

        // TODO: assert URL is just the SCIM ME endpoint
        $response = $testCase->post(
            $json['url'],
            [
                "schemas" => [
                    "urn:ietf:params:scim:schemas:core:2.0:User"
                ],
                "urn:ietf:params:scim:schemas:core:2.0:User" => [
                    "emails" => [
                        [
                            "value" => "johndoe@example.com"
                        ]
                    ]
                ]
            ]
        );

        $response->assertStatus(201);
        $response->assertHeader('x-scim-proof-of-creation');
        $proofOfCreation = $response->baseResponse->headers->get('x-scim-proof-of-creation');

        $response = $testCase->post(
            sprintf('https://master.test.dev/api/authchain/v2/p/%s', urlencode($module['id'])),
            [
                'proof-of-creation' => $proofOfCreation
            ],
            [
                'X-StateId' => $stateId
            ]
        );

        $response->assertStatus(200);

        $response->assertHeader('X-AuthRequest');

        return $response;

    }

    public static function chooseFacebook(TestCase $testCase, $json, $data)
    {

        $provider = Mockery::mock('Laravel\Socialite\Contracts\Provider');

        $factory = Mockery::mock(\Laravel\Socialite\Contracts\Factory::class);
        $factory->shouldReceive('driver')->andReturn($provider);
        $factory->shouldReceive('buildProvider')->andReturn($provider);

        app()->instance(\Laravel\Socialite\Contracts\Factory::class, $factory);

        $abstractUser = Mockery::mock('Laravel\Socialite\Two\User');
        
        // Get the api user object here
        $abstractUser->shouldReceive('getId') 
            ->andReturn(1234)
            ->shouldReceive('getEmail')
            ->andReturn(Str::random(10).'@noemail.app')
            ->shouldReceive('getNickname')
            ->andReturn('Laztopaz')
            ->shouldReceive('getName')
            ->andReturn('Laztopaz')
            ->shouldReceive('getAvatar')
            ->andReturn('https://en.gravatar.com/userimage');
        
        $provider->shouldReceive('buildProvider')->andReturn($provider);
        $provider->shouldReceive('stateless')->andReturn($provider);
        $provider->shouldReceive('with')->andReturn($provider);
        $provider->shouldReceive('user')->andReturn($abstractUser);
        $provider->shouldReceive('redirect')->andReturn(redirect('https://does-not-matter?state='));

        $module = collect($json['next'])->first(
            function ($value, $key) {
                return $value['type'] == 'facebook';
            }
        );

        $testCase->assertNotNull($module);

        $response = $testCase->post(
            sprintf('/api/authchain/v2/p/%s', urlencode($module['id'])),
            array_merge(
                [
                'init' => true
                ], $data
            ),
            [
                'X-StateId' => $json['stateId']
            ]
        );

        //TODO: test wrong input, should be 422?? when wrong password
        $response->assertStatus(200);
        $response->assertHeader('X-AuthRequest');

        $response->assertJsonStructure(['url']);

        $response = $testCase->get('/login/social/callback/facebook?code=123&state=' . $json['stateId']);

        $response->assertStatus(302);

        return $response;
    }

    public static function choosePassword(TestCase $testCase, $json, $data)
    {

        $module = collect($json['next'])->first(
            function ($value, $key) {
                return $value['type'] == 'password';
            }
        );

        $testCase->assertNotNull($module);

        $response = $testCase->post(
            sprintf('/api/authchain/v2/p/%s', urlencode($module['id'])),
            array_merge(
                [
                'username' => 'arietimmerman@gmail.com',
                'password' => '1234',
                'remember' => true
                ], $data
            ),
            [
                'X-StateId' => $json['stateId']
            ]
        );

        //TODO: test wrong input, should be 422?? when wrong password
        $response->assertStatus(200);
        $response->assertHeader('X-AuthRequest');

        return $response;
    }

    public static function chooseConsent(TestCase $testCase, $json, $data)
    {

        $module = collect($json['next'])->first(
            function ($value, $key) {
                return $value['type'] == 'consent';
            }
        );

        $testCase->assertNotNull($module);

        $response = $testCase->post(
            sprintf('/api/authchain/v2/p/%s', urlencode($module['id'])),
            [
            ],
            [
                'X-StateId' => $json['stateId']
            ]
        );

        $response->assertStatus(200);
        $response->assertHeader('X-AuthRequest');

        return $response;

    }

    public static function choosePasswordless(TestCase $testCase, $json, $data)
    {

        $module = collect($json['next'])->first(
            function ($value, $key) {
                return $value['type'] == 'passwordless';
            }
        );

        $testCase->assertNotNull($module);

        Mail::fake();

        $response = $testCase->post(
            sprintf('/api/authchain/v2/p/%s', urlencode($module['id'])),
            [
                'username' => 'arietimmerman@gmail.com'
            ],
            [
                'X-StateId' => $json['stateId']
            ]
        );

        $response->assertStatus(200);
        $response->assertHeader('X-AuthRequest');

        $link = null;
        Mail::assertSent(
            StandardMail::class, function (Mailable $mail) use (&$link) {
                $render = $mail->build();

                preg_match('/href="(.*?)"/', $render, $matches);
            
                $link = $matches[1];

                return $mail->hasTo('arietimmerman@gmail.com');
            }
        );

        $response = $testCase->get($link);

        // Always a 302 should occur
        $response->assertStatus(302);

        return $response;

    }


    protected function getHandler()
    {
        return new MockHandler(
            [
                function ($request, $options) {
                    
                    $query = parse_query((string) $request->getBody());

                    $response = $this->testCase->call($request->getMethod(), $request->getUri()->getPath(), $query);

                    return new Response($response->baseResponse->getStatusCode(), $response->baseResponse->headers->all(), $response->baseResponse->getContent());
                    
                },
                function ($request, $options) {
                    
                    $headers = collect($request->getHeaders())->map(
                        function ($value) {
                            return $value[0];
                        }
                    )->toArray();
                    
                    $response = $this->testCase->get($request->getUri()->getPath(), $headers);

                    return new Response($response->baseResponse->getStatusCode(), $response->baseResponse->headers->all(), $response->baseResponse->getContent());
                    
                }
            ]
        );
    }



}