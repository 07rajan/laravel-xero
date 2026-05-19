<?php

namespace App\Services\Xero;

use Illuminate\Support\Facades\Crypt;
use App\Models\Xero\XeroToken;

class XeroTokenService
{
    public function saveToken(
        $userId,
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