<?php

namespace Xerointegration\LaravelXero\Models\Xero;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class XeroToken extends Model
{
    use HasFactory;

    protected static $logName = 'XeroToken';
    protected static $logOnlyDirty = true;
    protected static $logAttributes = [
        "access_token",
        "expires_at",
        "refresh_token",
        "tenant_id",
        "tenant_type",
        "tenant_name",
        "added_by",
        "added_at"
    ];
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        "access_token",
        "expires_at",
        "refresh_token",
        "tenant_id",
        "tenant_type",
        "tenant_name",
        "added_by",
        "added_at"
    ];
    protected $table = 'xero_tokens';
    protected $primaryKey = 'id';
    protected $guarded = ['id'];

}
