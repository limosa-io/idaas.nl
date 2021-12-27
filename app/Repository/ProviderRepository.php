<?php

namespace App\Repository;

use App\OpenIDProvider;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\AuthLevel;
use Idaas\Passport\ProviderRepository as IdaasProviderRepository;

class ProviderRepository extends IdaasProviderRepository
{

    protected $validations;

    function __construct()
    {

        $this->validations  = [
            'liftime_access_token' => 'required|numeric|min:0',
            'liftime_refresh_token' => 'required|numeric|min:0',
            'liftime_id_token' => 'required|numeric|min:0',
            'acr_values_supported' => 'nullable|array|distinct',
            'response_types_supported' => 'nullable|array|distinct',

            'profile_url_template' => 'nullable',

            'init_url' => 'nullable|url'
        ];
    }

    public function get()
    {
        return ($o = OpenIDProvider::first())->makeVisible($o->getHidden());
    }

    public function wellknown()
    {
        return OpenIDProvider::first();
    }

    public function update(Request $request)
    {

        $validator = Validator::make($request->all(), $this->validations);

        $data = $validator->validate();

        $acrValuesSupported = $data['acr_values_supported'] ?? [];
        unset($data['acr_values_supported']);

        $provider = OpenIDProvider::first();
        $provider->forceFill($data);
        $provider->save();

        AuthLevel::where(['type' => 'oidc'])->whereNotIn('level', $acrValuesSupported)->delete();

        foreach ($acrValuesSupported as $value) {
            AuthLevel::firstOrCreate(array('type' => 'oidc', 'level' => $value, 'provider_id' => OpenIDProvider::first()->id));
        }

        return $provider;
    }
}
