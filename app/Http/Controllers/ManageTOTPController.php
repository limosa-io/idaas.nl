<?php

/**
 * Used to generate an HOTP secret.
 *
 * This in turn must be stored using regular SCIM.
 */

namespace App\Http\Controllers;

use App\AuthTypes\TOTP;

class ManageTOTPController extends Controller
{
    public function generateSecuret()
    {
        return ['secret' => TOTP::generateSecret()];
    }
}
