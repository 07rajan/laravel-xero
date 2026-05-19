<?php
namespace App\Services\Xero;

class XeroConfigService
{
    public function getConfig()
    {
        return [
            'client_id' => config('xero.client_id'),
            'client_secret' => config('xero.client_secret'),
            'redirect_uri' => config('xero.redirect_uri')
        ];
    }
}