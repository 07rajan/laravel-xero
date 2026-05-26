<?php

namespace Xerointegration\LaravelXero\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class XeroToken extends Model
{
    use HasFactory;

    protected static $logName = 'XeroToken';
    protected static $logOnlyDirty = true;
    protected static $logAttributes = [
        "project_id",
        "access_token",
        "expires_at",
        "refresh_token",
        "tenant_id"
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        "project_id",
        "access_token",
        "expires_at",
        "refresh_token",
        "tenant_id"
    ];
    protected $table = 'xero_tokens';
    protected $primaryKey = 'id';
    protected $guarded = ['id'];

}
