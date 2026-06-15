<?php

namespace Xerointegration\LaravelXero\Services;

use Exception;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Crypt;
use XeroAPI\XeroPHP\Configuration;
use Xerointegration\LaravelXero\Models\XeroToken;

class XeroApiService
{
    public function execute(
        $apiClass,
        callable $callback
    ) {
        $connection = XeroToken::where('project_id',config('xero.project_id'))->first();
        if (!$connection) {
            throw new Exception(
                'Xero connection not found'
            );
        }
        try {
            if (now()->gte($connection->expires_at)) {
                $newToken = app(XeroRefreshTokenService::class)->refresh($connection);
                $accessToken = $newToken->getToken();
            } else {
                $accessToken = Crypt::decryptString($connection->access_token);
            }
            $api = $this->makeApi($apiClass,$accessToken);
            return $callback($api, $connection->tenant_id);

        } catch (Exception $e) {
            if (
                method_exists($e, 'getCode') &&
                $e->getCode() == 401
            ) {
                $newToken = app(
                    XeroRefreshTokenService::class
                )->refresh($connection);
                 $api = $this->makeApi(
                    $apiClass,
                    $newToken->getToken()
                );
                return $callback($api, $connection->tenant_id);
            }
            throw $e;
        }
    }

    protected function makeApi(
        $apiClass,
        $accessToken
    ) {
        $config =
            Configuration::getDefaultConfiguration()
                ->setAccessToken(
                    $accessToken
                );

        return new $apiClass(
            new Client(),
            $config
        );
    }
}