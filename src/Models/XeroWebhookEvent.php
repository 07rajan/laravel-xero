<?php

namespace XeroIntegration\LaravelXero\Models;

use Illuminate\Database\Eloquent\Model;

class XeroWebhookEvent extends Model
{
    protected $table = 'xero_webhook_events';
 
    protected $fillable = [
        'tenant_id',
        'event_id',
        'resource_id',
        'resource_type',
        'event_type',
        'event_date',
        'payload',
        'status',
        'retry_count',
        'error',
        'processed_at',
    ];

    protected $casts = [
        'payload'      => 'array',
        'event_date'   => 'datetime',
        'processed_at' => 'datetime',
    ];

    const STATUS_PENDING = 'pending';
    const STATUS_PROCESSING = 'processing';
    const STATUS_COMPLETED = 'completed';
    const STATUS_FAILED = 'failed';
}