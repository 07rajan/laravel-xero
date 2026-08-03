<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('xero_webhook_events', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id')->nullable();
            $table->string('event_id')->nullable();
            $table->string('resource_id');
            $table->string('resource_type');
            $table->string('event_type');
            $table->timestamp('event_date')->nullable();
            $table->json('payload');
            $table->enum('status',[
                'pending',
                'processing',
                'completed',
                'failed'
            ])->default('pending');
            $table->integer('retry_count')->default(0);
            $table->text('error')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('xero_webhook_events');
    }
};