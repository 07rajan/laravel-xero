<?php

namespace Xerointegration\LaravelXero\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use DB;
use Xerointegration\LaravelXero\Services\XeroAuthService;
use Xerointegration\LaravelXero\Services\XeroTokenService;
use Illuminate\Routing\Controller;
use Xerointegration\LaravelXero\Models\XeroToken;

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
        try {
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
                $tenantId,
                $token
            );
            return redirect(
                config('xero.landing_uri')
            );
        }
        catch (Exception $err) {
            return $this->error("Something went wrong. Please try again later.", 500);
        }
    }

    public function checkXeroStatus() {
        try {
            $connection = XeroToken::where('project_id',config('xero.project_id'))->first();
            return response()->json([
                'status' => true,
                'message' => "Xero data fetched successfully.",
                'data' => $connection,
            ], 200);
        }
        catch (Exception $err) {
            return $this->error("Something went wrong. Please try again later.", 500);
        }
    }
}
