<?php

namespace Tests\Feature;

use App\ModuleResult;
use App\OAuthScope;
use App\RemoteServiceProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\Passport;
use Tests\Helper\OpenIDHelper;
use Tests\Helper\SAMLHelper;
use Tests\TestCase;

class SAMLTest extends TestCase
{
    use RefreshDatabase;

    public function testMetadata()
    {
        $response = $this->get('/saml/v2/metadata.xml');
        $response->assertStatus(200);
    }

    public function testLoginLogout($remember = true)
    {
        $moduleResults = ModuleResult::get();

        $this->assertCount(0, $moduleResults);

        $response = $this->post(
            'https://master.manage.test.dev/api/saml/manage/serviceproviders', [
                'entityid' => 'http://localhost:9080/simplesaml/module.php/saml/sp/metadata.php/default-sp',
                'wantSignedAuthnResponse' => true,
                'wantSignedAssertions' => true,
                'wantSignedLogoutResponse' => false,
                'wantSignedLogoutRequest' => false,

                'SingleLogoutService' => [
                    ['Binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect', 'Location' => 'http://localhost:9080/simplesaml/module.php/saml/sp/saml2-logout.php/default-sp', 'index' => '0'],
                ],

                'AssertionConsumerService' => [
                    ['Binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST', 'Location' => 'http://localhost:9080/simplesaml/module.php/saml/sp/saml2-acs.php/default-sp', 'index' => '0'],
                ],
            ], [
                'Authorization' => sprintf('Bearer %s', $this->getAccessToken()),
            ]
        );
        $response->assertStatus(201);

        $remoteServiceProvider = RemoteServiceProvider::where('entityid', 'http://localhost:9080/simplesaml/module.php/saml/sp/metadata.php/default-sp')->first();

        $this->assertNotNull($remoteServiceProvider);

        $response = $this->post(
            'https://master.test.dev/saml/v2/login', [
                'SAMLRequest' => base64_encode(
                    <<<'SAML'
<samlp:AuthnRequest xmlns:samlp="urn:oasis:names:tc:SAML:2.0:protocol" xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion" ID="_9e08ac832054f79e380c463b7baea30949ff129765" Version="2.0" IssueInstant="2019-07-22T19:05:44Z" Destination="https://master.test.dev/saml/v2/login" AssertionConsumerServiceURL="http://localhost:9080/simplesaml/module.php/saml/sp/saml2-acs.php/default-sp" ProtocolBinding="urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST">
<saml:Issuer>http://localhost:9080/simplesaml/module.php/saml/sp/metadata.php/default-sp</saml:Issuer>
<samlp:NameIDPolicy Format="urn:oasis:names:tc:SAML:2.0:nameid-format:transient" AllowCreate="true" />
</samlp:AuthnRequest>
SAML
                ),
                'RelayState' => 'relay-state-test',
            ]
        );

        $response->assertStatus(302);

        $loginStateHelper = new SAMLHelper($this, $response);

        $helper = $loginStateHelper->expect(
            'password', [
                'remember' => $remember,
            ]
        )->expectSAMLFinish();

        $response = $this->call(
            'GET', 'https://master.test.dev/isLoggedIn', [], collect($helper->response->baseResponse->headers->getCookies())->mapWithKeys(
                function ($value, $key) {
                    return [$value->getName() => $value->getValue()];
                }
            )->toArray()
        );

        $response->assertStatus(200);

        $this->assertEquals($remember ? 'true' : 'false', trim(''.$response->baseResponse->getContent()));

        $moduleResults = ModuleResult::get();

        $this->assertCount($remember ? 1 : 0, $moduleResults);

        $logoutMessage = <<<'SAML'
<samlp:LogoutRequest xmlns:samlp="urn:oasis:names:tc:SAML:2.0:protocol" xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion" ID="_b58cadae88f2008d068ff0cba655ca998cd7a26510" Version="2.0" IssueInstant="2019-07-22T19:47:13Z" Destination="https://master.idaas.dev/saml/v2/logout">
    <saml:Issuer>http://localhost:9080/simplesaml/module.php/saml/sp/metadata.php/default-sp</saml:Issuer>
    <saml:NameID SPNameQualifier="http://localhost:9080/simplesaml/module.php/saml/sp/metadata.php/default-sp" Format="urn:oasis:names:tc:SAML:2.0:nameid-format:transient">8e3aa254-3678-4504-8224-9b3733b4c275</saml:NameID>
</samlp:LogoutRequest>
SAML;

        $response = $this->call(
            'GET',
            sprintf(
                '/saml/v2/logout?SAMLRequest=%s&RelayState=%s',
                urlencode(base64_encode(gzdeflate($logoutMessage))),
                'relay-state-logout'
            ),
            [],
            collect($helper->response->baseResponse->headers->getCookies())
                ->mapWithKeys(
                    function ($value, $key) {
                        return [$value->getName() => $value->getValue()];
                    }
                )->toArray()
        );

        $response->assertStatus(302);

        $query = parse_url($response->baseResponse->headers->get('location'), PHP_URL_QUERY);
        parse_str($query, $result);

        $this->assertArrayHasKey('RelayState', $result);
        $this->assertEquals('relay-state-logout', $result['RelayState']);

        //FIXME: Ensure sessions gets deleted upon logout
        $response = $this->call(
            'GET', '/isLoggedIn', [], collect($helper->response->baseResponse->headers->getCookies())->mapWithKeys(
                function ($value, $key) {
                    return [$value->getName() => $value->getValue()];
                }
            )->toArray()
        );

        $this->assertEquals('false', trim($response->baseResponse->getContent()));
    }

    public function testWithoutRemember()
    {
        $this->testLoginLogout(false);
    }

    public function testImportMetadata()
    {
        $metadata = <<<'METADATA'
<?xml version="1.0"?>
<md:EntityDescriptor xmlns:md="urn:oasis:names:tc:SAML:2.0:metadata" entityID="http://localhost:9080/simplesaml/module.php/saml/sp/metadata.php/default-sp">
    <md:SPSSODescriptor protocolSupportEnumeration="urn:oasis:names:tc:SAML:1.1:protocol urn:oasis:names:tc:SAML:2.0:protocol">
    <md:SingleLogoutService Binding="urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect" Location="http://localhost:9080/simplesaml/module.php/saml/sp/saml2-logout.php/default-sp"/>
    <md:AssertionConsumerService Binding="urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST" Location="http://localhost:9080/simplesaml/module.php/saml/sp/saml2-acs.php/default-sp" index="0"/>
    <md:AssertionConsumerService Binding="urn:oasis:names:tc:SAML:1.0:profiles:browser-post" Location="http://localhost:9080/simplesaml/module.php/saml/sp/saml1-acs.php/default-sp" index="1"/>
    <md:AssertionConsumerService Binding="urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Artifact" Location="http://localhost:9080/simplesaml/module.php/saml/sp/saml2-acs.php/default-sp" index="2"/>
    <md:AssertionConsumerService Binding="urn:oasis:names:tc:SAML:1.0:profiles:artifact-01" Location="http://localhost:9080/simplesaml/module.php/saml/sp/saml1-acs.php/default-sp/artifact" index="3"/>
    </md:SPSSODescriptor>
    <md:ContactPerson contactType="technical">
    <md:GivenName>Administrator</md:GivenName>
    <md:EmailAddress>dublindev@glgroup.com</md:EmailAddress>
    </md:ContactPerson>
</md:EntityDescriptor>
METADATA;

        $response = $this->post(
            'https://master.manage.test.dev/api/saml/manage/importMetadata', [
                'metadata' => $metadata,
            ], [
                'Authorization' => sprintf('Bearer %s', $this->getAccessToken()),
            ]
        );

        $response->assertStatus(201);

        $this->assertNotNull(RemoteServiceProvider::where('entityid', 'http://localhost:9080/simplesaml/module.php/saml/sp/metadata.php/default-sp')->first());
    }

    public function testLoginLogoutRedirect($remember = true)
    {

        $scopes = OAuthScope::all()->pluck('description', 'name')->all();

        Passport::tokensCan($scopes);

        $helper = SAMLHelper::initWithNewServiceProvider($this)->expect(
            'password', [
                'remember' => $remember,
            ]
        )->expectSAMLRedirectFinish();

        if ($remember) {

            // FIXME: Should login without prompt
            OpenIDHelper::initWithNewClient(
                $this, [
                    'trusted' => true,
                    'grant_type' => [
                        'authorization_code',
                        'implicit',
                    ],
                    'response_types' => [
                        'code',
                        'token',
                        'id_token',
                    ],
                ], [], collect($helper->response->baseResponse->headers->getCookies())
                    ->mapWithKeys(
                        function ($value, $key) {
                            return [$value->getName() => $value->getValue()];
                        }
                    )->toArray()
            )->expect('password')->expectFinish();
        }

        $response = $this->call(
            'GET', '/isLoggedIn', [], collect($helper->response->baseResponse->headers->getCookies())->mapWithKeys(
                function ($value, $key) {
                    return [$value->getName() => $value->getValue()];
                }
            )->toArray()
        );

        $response->assertStatus(200);

        $this->assertEquals($remember ? 'true' : 'false', trim(''.$response->baseResponse->getContent()));

        $logoutMessage = <<<'SAML'
<samlp:LogoutRequest xmlns:samlp="urn:oasis:names:tc:SAML:2.0:protocol" xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion" ID="_b58cadae88f2008d068ff0cba655ca998cd7a26510" Version="2.0" IssueInstant="2019-07-22T19:47:13Z" Destination="https://master.idaas.dev/saml/v2/logout">
    <saml:Issuer>http://localhost:9080/simplesaml/module.php/saml/sp/metadata.php/default-sp</saml:Issuer>
    <saml:NameID SPNameQualifier="http://localhost:9080/simplesaml/module.php/saml/sp/metadata.php/default-sp" Format="urn:oasis:names:tc:SAML:2.0:nameid-format:transient">8e3aa254-3678-4504-8224-9b3733b4c275</saml:NameID>
</samlp:LogoutRequest>
SAML;

        $response = $this->call(
            'GET',
            sprintf(
                '/saml/v2/logout?SAMLRequest=%s&RelayState=%s',
                urlencode(base64_encode(gzdeflate($logoutMessage))),
                'relay-state-logout'
            ),
            [],
            collect($helper->response->baseResponse->headers->getCookies())
                ->mapWithKeys(
                    function ($value, $key) {
                        return [$value->getName() => $value->getValue()];
                    }
                )->toArray()
        );

        $response->assertStatus(302);

        $query = parse_url($response->baseResponse->headers->get('location'), PHP_URL_QUERY);
        parse_str($query, $result);

        $this->assertArrayHasKey('RelayState', $result);
        $this->assertEquals('relay-state-logout', $result['RelayState']);

        //FIXME: Ensure sessions gets deleted upon logout
        $response = $this->call(
            'GET', '/isLoggedIn', [], collect($helper->response->baseResponse->headers->getCookies())->mapWithKeys(
                function ($value, $key) {
                    return [$value->getName() => $value->getValue()];
                }
            )->toArray()
        );

        $this->assertEquals('false', trim($response->baseResponse->getContent()));
    }
}
