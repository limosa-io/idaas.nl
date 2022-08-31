<?php

/**
 * Used by the login form
 */

namespace App\Http\Controllers;

use App\AuthChain;
use App\AuthLevel;
use App\AuthModule;
use App\Client;
use App\EmailTemplate;
use App\TenantSetting;
use Illuminate\Http\Request;

class ImportController extends Controller
{
    public function index(Request $request)
    {
        $content = $request->input('yaml');

        $yaml = \yaml_parse($content);

        $clients = [];
        foreach ($clients as $client) {
            // TODO: check if client with id exists
            // TODO: override all properties
        }
    }

    protected static function filterNull($array)
    {
        return array_filter(
            $array,
            function ($value) {
                return !is_null($value);
            }
        );
    }

    protected static function filterDates($array)
    {
        return collect($array)->filter(
            function ($value, $key) {
                return $key != 'created_at' && $key != 'updated_at';
            }
        )->all();
    }

    public function export()
    {
        $clients = [];

        foreach (Client::all() as $client) {
            $client = self::filterNull($client->toArray());
            $client = self::filterDates($client);
            $clients[] = $client;
            //TODO: filter created, updated,
        }

        $authLevels = [];

        foreach (AuthLevel::all() as $authLevel) {
            $authLevel = self::filterNull($authLevel->toArray());
            $authLevel = self::filterDates($authLevel);

            unset($authLevel['id']);

            $authLevels[] = $authLevel;
        }

        $authModules = [];

        foreach (AuthModule::all() as $authModule) {
            $module = self::filterNull($authModule->toArray());
            $module = self::filterDates($module);

            unset($module['id']);

            // TODO: Refer to auth level with anchors??
            $module['auth_levels'] = [
                '*test123'
            ];

            $authModules[] = $module;
        }

        $authChains = [];

        foreach (AuthChain::all() as $authChain) {
            $authChain = self::filterNull($authChain->toArray());
            $authChain = self::filterDates($authChain);

            unset($authChain['id']);

            $authChains[] = $authChain;
        }

        $emails = [];

        foreach (EmailTemplate::all() as $emailTemplate) {
            $emailTemplate = self::filterNull($authChain->toArray());
            $emailTemplate = self::filterDates($authChain);

            unset($emailTemplate['id']);

            $emails[] = $emailTemplate;
        }

        $settings = [];

        foreach (TenantSetting::all() as $setting) {
            $settings[$setting->key] = $setting->value;
        }

        $yaml = [
            'clients' => $clients,
            'authentication_levels' => $authLevels,
            'authentication_modules' => $authModules,
            'authentication_chain' => $authChains,
            'settings' => $settings,
            'emails' => $emails

        ];

        return \yaml_emit($yaml);
    }
}
