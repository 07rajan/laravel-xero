<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('xero_tokens', function (
            Blueprint $table
        ) {
            $table->id();
            $table->string('project_id');
            $table->text('access_token')->nullable();
            $table->text('refresh_token')->nullable();
            $table->string('expires_at')->nullable();
            $table->string('tenant_id')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('xero_tokens');
    }
};