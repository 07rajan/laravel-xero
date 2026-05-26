<?php
namespace Xerointegration\LaravelXero\Services;

use XeroAPI\XeroPHP\Configuration;
use League\OAuth2\Client\Provider\GenericProvider;
use Xerointegration\LaravelXero\Services\XeroConfigService;

class XeroAuthService
{
    protected $provider;

    public function __construct(
        XeroConfigService $configService
    ) {

        $config = $configService->getConfig();

        $this->provider = new GenericProvider([
            'clientId'                => $config['client_id'],
            'clientSecret'            => $config['client_secret'],
            'redirectUri'             => $config['redirect_uri'],
            'urlAuthorize'            => 'https://login.xero.com/identity/connect/authorize',
            'urlAccessToken'          => 'https://identity.xero.com/connect/token',
            'urlResourceOwnerDetails' => '',
        ]);
    }

    public function getAuthorizationUrl()
    {
        return $this->provider->getAuthorizationUrl([
           'scope' => 'openid profile email offline_access accounting.settings accounting.contacts',
            'access_type' => 'offline'
        ]);
    }

    public function getProvider()
    {
        return $this->provider;
    }
}