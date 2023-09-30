<?php

namespace Tests\Helper;

use Illuminate\Support\Str;
use Lcobucci\JWT\Configuration;
use Tests\TestCase;

class OpenIDHelper extends LoginStateHelper
{
    public static function initWithNewClient(TestCase $testCase, array $clientData = [], array $data = [], $cookies = [])
    {

        $clientData = array_merge(
            [
                'client_name' => 'test',
                'application_type' => 'web',
                'public' => 'public',
                'redirect_uris' => [
                    'https://what.ever.com',
                ],
            ],
            $clientData
        );

        $data = array_merge(
            [
                'redirect_uri' => 'https://what.ever.com',
            ],
            $data
        );

        $response = $testCase->post(
            'https://master.test.dev/oauth/connect/register',
            $clientData,
            [
                'Authorization' => sprintf('Bearer %s', $testCase->getAccessToken()),
            ]
        );

        $response->assertStatus(201);
        $clientId = $response->json('client_id');

        return self::init($testCase, $clientId, $data, $cookies);
    }

    /**
     * @return self
     */
    public static function init(TestCase $testCase, $clientId, array $data = [], array $cookies = [])
    {

        $data = array_merge(
            [
                'response_type' => 'code',
                'prompt' => 'login',
                'redirect_uri' => route('ice.manage.completelogin'),
                'nonce' => '1234',
                'state' => '4567',
                'client_id' => $clientId,
                'scope' => ['openid', 'applications:manage'],
            ],
            $data
        );

        if (! is_array($data['scope'])) {
            $data['scope'] = explode(' ', $data['scope']);
        }

        $query = collect($data)->map(
            function ($value, $key) {
                if ($key == 'scope' && is_array($value)) {
                    $value = implode(' ', $value);
                }

                return sprintf('%s=%s', $key, urlencode($value));
            }
        )->implode('&');

        $response = $testCase->call(
            'GET',
            sprintf(
                '%s?%s',
                route('oauth.authorize'),
                $query
            ),
            [],
            $cookies
        );

        // A redirect is always expected
        $response->assertStatus(302);
        $response->assertHeader('Location');

        return new static($testCase, $response, $data);
    }

    public function expectAutoFinish($expectError = false, $error = 'access_denied')
    {

        $response = $this->response;
        $this->testCase->assertStringStartsWith($this->data['redirect_uri'], $response->baseResponse->headers->get('location'));

        $completeUri = $response->baseResponse->headers->get('Location');
        $query = parse_url($completeUri, PHP_URL_QUERY);
        parse_str($query, $result);

        if ($expectError) {
            $this->testCase->assertArrayHasKey('error', $result);
            $this->testCase->assertEquals($error, $result['error']);
        } else {
            // FIXME: should also trigger if no error. However, state is not send with exceptions ...
            $this->testCase->assertArrayHasKey('state', $result);
            $this->testCase->assertEquals($this->data['state'], $result['state']);
        }

        return new self($this->testCase, $response, $this->data);
    }

    public function expectToken($place = 'fragment')
    {

        $location = $this->response->baseResponse->headers->get('Location');
        $query = parse_url($location, $place == 'query' ? PHP_URL_QUERY : PHP_URL_FRAGMENT);
        parse_str($query, $result);

        $this->testCase->assertArrayHasKey('access_token', $result);

        if (in_array('openid', $this->data['scope'])) {
            $this->testCase->assertArrayHasKey('id_token', $result);

            $configuration = Configuration::forUnsecuredSigner();

            $parser = $configuration->parser();
            $token = $parser->parse($result['id_token']);

            $this->testCase->assertEquals($this->data['nonce'], $token->claims()->get('nonce'));

            // $this->testCase->assertEquals(url('/'), $token->getClaim('iss'));
        } else {
            $this->testCase->assertArrayNotHasKey('id_token', $result);
        }
    }

    public function expectCodeToToken($place = 'query')
    {

        $location = $this->response->baseResponse->headers->get('Location');
        $query = parse_url($location, $place == 'query' ? PHP_URL_QUERY : PHP_URL_FRAGMENT);
        parse_str($query, $result);

        $this->testCase->assertFalse(empty($result));

        $this->testCase->assertArrayHasKey('code', $result);

        $data = [
            'grant_type' => 'authorization_code',
            'code' => $result['code'],
            'redirect_uri' => $this->data['redirect_uri'],
            'client_id' => $this->data['client_id'],
        ];
        $response = $this->testCase->post(route('ice.login.ui').'/token', $data);

        $response->assertOk();
        $json = $response->json();

        $this->testCase->assertArrayHasKey('access_token', $json);
        $this->testCase->assertArrayHasKey('refresh_token', $json);
        $this->testCase->assertArrayHasKey('token_type', $json);
        $this->testCase->assertArrayHasKey('expires_in', $json);

        if (in_array('openid', $this->data['scope'])) {
            $this->testCase->assertArrayHasKey('id_token', $json);
            $configuration = Configuration::forUnsecuredSigner();

            $parser = $configuration->parser();
            $token = $parser->parse($json['id_token']);

            $this->testCase->assertEquals($this->data['nonce'], $token->claims()->get('nonce'));

            $this->testCase->assertEquals(url('/'), $token->claims()->get('iss'));
        } else {
            $this->testCase->assertArrayNotHasKey('id_token', $json);
        }
    }

    public function expectFinish()
    {

        $helper = parent::expectFinish();
        $response = $helper->response;
        $this->testCase->assertStringStartsWith($this->data['redirect_uri'], $response->baseResponse->headers->get('location'));

        $completeUri = $response->baseResponse->headers->get('Location');
        if (Str::contains($this->data['response_type'], 'token')) {
            $query = parse_url($completeUri, PHP_URL_FRAGMENT);
        } else {
            $query = parse_url($completeUri, PHP_URL_QUERY);
        }
        parse_str($query, $result);

        $this->testCase->assertArrayHasKey('state', $result);
        $this->testCase->assertEquals($this->data['state'], $result['state']);

        return new self($this->testCase, $response, $this->data);
    }

    public function expectFinishWithError($error = 'access_denied')
    {
        $helper = parent::expectFinish();
        $response = $helper->response;
        $this->testCase->assertStringStartsWith($this->data['redirect_uri'], $response->baseResponse->headers->get('location'));

        $completeUri = $response->baseResponse->headers->get('Location');
        $query = parse_url($completeUri, PHP_URL_QUERY);
        parse_str($query, $result);

        $this->testCase->assertArrayHasKey('error', $result);
        $this->testCase->assertEquals($error, $result['error']);

        // $this->testCase->assertArrayHasKey('state',$result);
        // $this->testCase->assertEquals($this->data['state'],$result['state']);

        return new self($this->testCase, $response, $this->data);
    }
}
