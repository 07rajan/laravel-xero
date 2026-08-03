<?php

namespace Xerointegration\LaravelXero\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Xerointegration\LaravelXero\Models\XeroWebhookEvent;
use Xerointegration\LaravelXero\Services\XeroWebhookProcessor;

class XeroWebhookJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected int $webhookId;

    public function __construct(int $webhookId)
    {
        $this->webhookId = $webhookId;
    }

    public function handle(): void
    {
        $webhook = XeroWebhookEvent::where('id', $this->webhookId)->first();
        if (!$webhook) {
            return;
        }
        $webhookProcessor = app(XeroWebhookProcessor::class);
        $webhookProcessor->process($webhook);
        $webhook->update([
            'status' => XeroWebhookEvent::STATUS_COMPLETED,
            'processed_at' => now(),
            'error' => null
        ]);
    }
}