<?php

namespace Tests\Helper;

use App\RemoteServiceProvider;
use Tests\TestCase;

class SAMLHelper extends LoginStateHelper
{
    /**
     * @return SAMLHelper
     */
    public static function initWithNewServiceProvider(TestCase $testCase, array $clientData = [], array $data = [])
    {

        $response = $testCase->post(
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
                    ['Binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect', 'Location' => 'http://localhost:9080/simplesaml/module.php/saml/sp/saml2-acs.php/default-sp', 'index' => '0'],
                ],
            ], [
                'Authorization' => sprintf('Bearer %s', $testCase->getAccessToken()),
            ]
        );
        $response->assertStatus(201);

        $remoteServiceProvider = RemoteServiceProvider::where('entityid', 'http://localhost:9080/simplesaml/module.php/saml/sp/metadata.php/default-sp')->first();

        $testCase->assertNotNull($remoteServiceProvider);

        return self::init($testCase, 'not used', $data);

    }

    public static function init(TestCase $testCase, $remoteServiceProviderId, array $data = [])
    {
        $response = $testCase->post(
            'https://master.test.dev/saml/v2/login', [
                'SAMLRequest' => base64_encode(
                    <<<'SAML'
<samlp:AuthnRequest xmlns:samlp="urn:oasis:names:tc:SAML:2.0:protocol" xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion" ID="_9e08ac832054f79e380c463b7baea30949ff129765" Version="2.0" IssueInstant="2019-07-22T19:05:44Z" Destination="https://master.test.dev/saml/v2/login" AssertionConsumerServiceURL="http://localhost:9080/simplesaml/module.php/saml/sp/saml2-acs.php/default-sp" ProtocolBinding="urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect">
<saml:Issuer>http://localhost:9080/simplesaml/module.php/saml/sp/metadata.php/default-sp</saml:Issuer>
<samlp:NameIDPolicy Format="urn:oasis:names:tc:SAML:2.0:nameid-format:transient" AllowCreate="true" />
</samlp:AuthnRequest>
SAML
                ),
                'RelayState' => 'relay-state-test',
            ]
        );

        $response->assertStatus(302);

        return new self($testCase, $response, $data);

    }

    public function expectSAMLRedirectFinish()
    {
        $decoded = $this->getDecoded();

        $response = $this->testCase->post(
            $decoded['info']['fin'],
            [
                'authRequest' => $decoded['stateId'],
            ],
            [
            ]
        );

        $response->assertStatus(302);

        return new self($this->testCase, $response, $this->data);
    }

    public function expectSAMLFinish()
    {
        $decoded = $this->getDecoded();

        $response = $this->testCase->post(
            $decoded['info']['fin'],
            [
                'authRequest' => $decoded['stateId'],
            ],
            [
            ]
        );

        $response->assertStatus(200);

        $content = $response->baseResponse->getContent();

        $this->testCase->assertStringContainsStringIgnoringCase('<form method="post"', $content);
        $this->testCase->assertStringContainsStringIgnoringCase('type="hidden" name="SAMLRequest"', $content);

        return new self($this->testCase, $response, $this->data);
    }
}
