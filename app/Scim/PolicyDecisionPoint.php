<?php
/**
 * Defines what SCIM actions and attributes may be called or modified.
 */
namespace App\Scim;

use ArieTimmerman\Laravel\SCIMServer\PolicyDecisionPoint as BasePolicyDecisionPoint;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;
use ArieTimmerman\Laravel\SCIMServer\ResourceType;
use ArieTimmerman\Laravel\SCIMServer\Exceptions\SCIMException;
use Lcobucci\JWT\Parser;
use App\TenantSetting;
use Illuminate\Support\Str;

class PolicyDecisionPoint extends BasePolicyDecisionPoint
{

    protected function areAttributesAllowed($attributesAllowed, $attributesProvided)
    {

        $short = [];

        foreach($attributesProvided as $key=>$value){

            if(is_array($value)) { continue;
            }

            if(strrpos($key, ":") !== false) {
                $short = substr($key, strrpos($key, ":") + 1);
            }else{
                $short = $key;
            }

            if($p = strpos($short, ".")) {
                $short = substr($short, 0,  $p);
            }

            if(!in_array($short, $attributesAllowed)) {
                throw new SCIMException(sprintf('Attribute "%s" is not allowed for me-endpoint requests. Allowed are: %s', $short, empty($attributesAllowed) ? 'none' : implode(', ', $attributesAllowed)));
            }
        }
        
    }

    public function isAllowed(Request $request, $operation, array $attributes, ResourceType $resourceType, ?Model $resourceObject, $isMe = false)
    {

        // This check relies on the fact that non-ME endpoints require extra authorization (different route middleware)
        if(!$isMe) {
            return true;
        }

        if(! (TenantSetting::where('key', 'registration:allow')->value('value') ?? false)) {
            return false;
        }

        $registrationSettings = TenantSetting::where('key', 'like', 'registration:%')->get()->mapWithKeys(
            function ($item) {
                return [ Str::after($item->key, 'registration:') => $item->value];
            }
        );

        if($operation == self::OPERATION_POST) {

            $this->areAttributesAllowed($registrationSettings['attributes_create'] ?? [], $attributes);

            return true;
        }

        if($operation == self::OPERATION_PATCH || $operation == self::OPERATION_PUT) {

            $this->areAttributesAllowed($registrationSettings['attributes_update'] ?? [], $attributes);

            // TODO: Check when last authentication was done (not token issue date, but subject creation date).
            // Only allow password change within last 30 seconds
            if (isset($attributes['urn:ietf:params:scim:schemas:core:2.0:User:emails'])) {


                $parser = resolve(Parser::class);
                $parsed = $parser->parse($request->bearerToken());
                $verified = $parsed->getClaim('verified');
                
                foreach($attributes['urn:ietf:params:scim:schemas:core:2.0:User:emails'] as $email){
                    
                    if(!isset($verified) || $verified->email != $email['value']) {
                        throw new SCIMException(sprintf('The provided email is not allowed for update requests because it was not confirmed'));
                    }
                    
                }

            }
            

            return true;
        }

        return false;
        
    }

}