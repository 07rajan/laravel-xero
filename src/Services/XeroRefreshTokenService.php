<?php

namespace Xerointegration\LaravelXero\Services;

use Illuminate\Support\Facades\Crypt;

class XeroRefreshTokenService
{
    public function refresh($connection)
    {
        $provider = app(
            XeroAuthService::class
        )->getProvider();

        $newToken = $provider->getAccessToken(
            'refresh_token',
            [
                'refresh_token' =>
                    Crypt::decryptString(
                        $connection->refresh_token
                    )
            ]
        );

        $connection->update([
            'access_token' =>
                Crypt::encryptString(
                    $newToken->getToken()
                ),

            'refresh_token' =>
                Crypt::encryptString(
                    $newToken->getRefreshToken()
                ),

            'expires_at' =>
                date('Y-m-d H:i:s',$newToken->getExpires())
        ]);
        return $newToken;
    }
}