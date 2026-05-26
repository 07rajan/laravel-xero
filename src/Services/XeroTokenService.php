<?php

namespace Xerointegration\LaravelXero\Services;

use Illuminate\Support\Facades\Crypt;
use Xerointegration\LaravelXero\Models\XeroToken;

class XeroTokenService
{
    public function saveToken(
        $tenantId,
        $token
    ) {

        return XeroToken::updateOrCreate(
            [
                "project_id" => config('xero.project_id'),
            ],
            [
                'tenant_id' => $tenantId,

                'access_token' =>
                    Crypt::encryptString(
                        $token->getToken()
                    ),

                'refresh_token' =>
                    Crypt::encryptString(
                        $token->getRefreshToken()
                    ),

                'expires_at' =>
                    now()->addSeconds(
                        $token->getExpires()
                    )
            ]
        );
    }
}