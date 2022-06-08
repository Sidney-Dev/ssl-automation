<?php

namespace App\Models;

use App\Builders\LetsEncryptCertificateBuilder;
use App\Collections\LetsEncryptCertificateCollection;
use App\Facades\LetsEncrypt;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;


class LetsEncryptCertificate extends Model
{

    /**
     * The "booted" method of the model.
     *
     * @return void
     */
    protected static function booted()
    {
        static::created(function ($domain) {
            $domain->renew();
        });
    }

    protected $guarded = [];

    protected $dates = ['last_renewed_at'];

    protected $casts = [
        'created' => 'boolean',
    ];


    public function getHasExpiredAttribute(): bool
    {
        return $this->last_renewed_at && $this->last_renewed_at->diffInDays(now()) >= 90;
    }

    public function domains()
    {
        return $this->hasMany(Domains::class);
    }
}
