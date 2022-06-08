<?php

namespace App\Models;

use App\Builders\LetsEncryptCertificateBuilder;
use App\Collections\LetsEncryptCertificateCollection;
use App\Facades\LetsEncrypt;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use App\Models\Domains;
use App\LetsEncrypt as LocalLetsEncrypt;
use App\Environments;

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

    public static function createCertificate($certificate, $subdomains) {

        $letsencrypt = new LocalLetsEncrypt;
        $env = new Environments;

        $letsencrypt->renameDir($certificate->domain, "-old");
        $aDomains = $letsencrypt->generate($certificate->domain, $subdomains); // returns false if successful

        if ($letsencrypt->error == false) {
            $env->certificateDeactivation($certificate->environmentID, $certificate->slug);
            $env->certificateDeletion($certificate->environmentID, $certificate->slug);

            if (is_array($aDomains)) {
                foreach ($aDomains as $domain) {
                    Domains::updateOrCreate([
                        'name' => trim($domain),
                        'lets_encrypt_certificate_id' => $certificate->id,
                    ]);
                }
            }

            $env->addCertificateToEnvironment($certificate->label, $certificate->environmentID, $certificate->domain);
            $updatedCertificate = LetsEncryptCertificate::where('domain', $certificate->domain)->first(); //go grab the updated slug by quering letsencryptcertificate model
            $env->certificateActivation($updatedCertificate->environmentID, $updatedCertificate->slug);

            $letsencrypt->removeDir($certificate->domain . "-old");
        } else {
            // TODO: Log message
        }
    }

    public function newEloquentBuilder($query): LetsEncryptCertificateBuilder
    {
        return new LetsEncryptCertificateBuilder($query);
    }

    public function newCollection(array $models = [])
    {
        return new LetsEncryptCertificateCollection($models);
    }

    public function getHasExpiredAttribute(): bool
    {
        return $this->last_renewed_at && $this->last_renewed_at->diffInDays(now()) >= 90;
    }

    public function renew()
    {
        return LetsEncrypt::renew($this);
    }

    public function renewNow(): self
    {
        return LetsEncrypt::renewNow($this);
    }

    public function domains()
    {
        return $this->hasMany(Domains::class);
    }
}
