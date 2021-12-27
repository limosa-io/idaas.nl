<?php

namespace App;

use Idaas\OpenID\AdvancedResourceServer;

use Psr\Http\Message\ServerRequestInterface;

class ResourceServerCustom extends AdvancedResourceServer
{
 
    public function validateAuthenticatedRequest(ServerRequestInterface $request)
    {
        
        $result = parent::validateAuthenticatedRequest($request);
        
        if(!$result->getAttribute('oauth_user_id')) {
            $result = $result->withAttribute('oauth_user_id', 'client_' . $result->getAttribute('oauth_client_id'));
        }
        
        return $result;
    }
}