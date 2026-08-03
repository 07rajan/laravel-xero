<?php
namespace XeroIntegration\LaravelXero\Services;

use Illuminate\Http\Request;
use Xerointegration\LaravelXero\Models\XeroWebhookEvent;
use Xerointegration\LaravelXero\Jobs\XeroWebhookJob;

class XeroWebhookService
{
    /**
     * Verify webhook signature.
     */
    public function verifySignature(string $payload, string $signature): bool
    {
        // $webhookKey = config('xero.webhook_key');
        $webhookKey = "E8WzSALOo3fQmdlSxFiuVxm9fNxMqdRZ3ga4KRuapoqIPmBiQHeEXeYvZaf/M3rrlieS05c8NCYm5qjjdKwvGQ=="; 

        $hash = base64_encode(
            hash_hmac(
                'sha256',
                $payload,
                $webhookKey,
                true
            )
        );

        return hash_equals($hash, $signature);
    }

    /**
     * Save webhook events.
     */
    public function storeEvents(array $payload): void
    {
        foreach ($payload['events'] as $event) {

            $webhookEvent = XeroWebhookEvent::create(
                [
                    'tenant_id'      => $event['tenantId'] ?? null,
                    'resource_id'    => $event['resourceId'],
                    'resource_type'  => $event['eventCategory'],
                    'event_type'     => $event['eventType'],
                    'event_date'     => $event['eventDateUtc'],
                    'payload'        => $event,
                    'status'         => 'pending',
                ]
            );
            echo $webhookEvent->id;
            XeroWebhookJob::dispatch($webhookEvent->id);
        }
    }
}