<?php

namespace Xerointegration\LaravelXero\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use DB;
use Xerointegration\LaravelXero\Services\XeroAuthService;
use Xerointegration\LaravelXero\Services\XeroTokenService;

class XeroController extends Controller
{
    public function connect(XeroAuthService $service) 
    {
        return redirect(
            $service->getAuthorizationUrl()
        );
    }

    public function callback(
        Request $request,
        XeroAuthService $authService,
        XeroTokenService $tokenService
    ) {

        $provider = $authService->getProvider();

        $token = $provider->getAccessToken(
            'authorization_code',
            [
                'code' => $request->code
            ]
        );

        $xero = new \XeroAPI\XeroPHP\Api\IdentityApi(
            new \GuzzleHttp\Client(),
            \XeroAPI\XeroPHP\Configuration
                ::getDefaultConfiguration()
                ->setAccessToken(
                    $token->getToken()
                )
        );

        $connections = $xero->getConnections();

        $tenantId =
            $connections[0]->getTenantId();

        $tokenService->saveToken(
            auth()->id(),
            $tenantId,
            $token
        );

        return 'Xero Connected';
    }
}
