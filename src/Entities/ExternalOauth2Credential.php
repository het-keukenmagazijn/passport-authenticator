<?php namespace Keukenmagazijn\PassportAuthenticator\Entities;

use Illuminate\Database\Eloquent\Model;

class ExternalOauth2Credential extends Model
{
    protected $fillable = [
        'app_name',
        'access_token',
        'refresh_token',
        'expires_at'
    ];
}
