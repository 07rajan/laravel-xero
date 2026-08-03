<?php

namespace Xerointegration\LaravelXero\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use DB;
use Xerointegration\LaravelXero\Services\XeroAuthService;
use Xerointegration\LaravelXero\Services\XeroTokenService;
use Xerointegration\LaravelXero\Services\XeroWebhookService;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
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

    public function checkXeroStatus(Request $request) {
        try {
            $connection = XeroToken::where(
                'project_id',
                config('xero.project_id')
            )->first();
            return response()->json([
                'data' => $connection
            ]);
        } catch (\Exception $err) {
            return response()->json([
                'status' => false
            ], 500);
        }
    }

    public function disconnectXero(Request $request) {
        try {
            $connection = XeroToken::where(
                'project_id',
                config('xero.project_id')
            )->delete();
            return response()->json([
                'status' => true
            ]);
        } catch (\Exception $err) {
            return response()->json([
                'status' => false
            ], 500);
        }
    }

    public function handleWebhook(Request $request)
    {
        $webhookService = app(XeroWebhookService::class);
        $payload = $request->getContent();

        $xeroSignature = $request->header('x-xero-signature');

        if (! $webhookService->verifySignature(
            $payload,
            $xeroSignature
        )) {
            return response()->json([
                'message' => 'Invalid Signature'
            ],401);
        }
        $requestData = json_decode($payload,true);


        $webhookService->storeEvents(
            $requestData
        );



        return response()->json([],200);
    }
}
