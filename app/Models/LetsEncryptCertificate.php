<?php

namespace App\Models;

use App\Builders\LetsEncryptCertificateBuilder;
use App\Collections\LetsEncryptCertificateCollection;
use App\Facades\LetsEncrypt;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

/**
 * @property int $id
 * @property string $domain
 * @property string|null $fullchain_path
 * @property string|null $chain_path
 * @property string|null $privkey_path
 * @property string|null $cert_path
 * @property bool $created
 * @property \Illuminate\Support\Carbon|null $last_renewed_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read bool $has_expired
 * @method static \App\Builders\LetsEncryptCertificateBuilder|\App\Models\LetsEncryptCertificate query()
 * @method static \App\Builders\LetsEncryptCertificateBuilder|\App\Models\LetsEncryptCertificate newQuery()
 * @method static \App\Builders\LetsEncryptCertificateBuilder|\App\Models\LetsEncryptCertificate newModelQuery()
 * @method static \App\Builders\LetsEncryptCertificateBuilder|\App\Models\LetsEncryptCertificate withTrashed()
 * @method static \App\Builders\LetsEncryptCertificateBuilder|\App\Models\LetsEncryptCertificate withoutTrashed()
 * @method static \App\Builders\LetsEncryptCertificateBuilder|\App\Models\LetsEncryptCertificate whereDomain($value)
 * @method static bool|null forceDelete()
 * @method static bool|null restore()
 * @method static static create(array $attributes)
 * @mixin \Eloquent
 */
class LetsEncryptCertificate extends Model
{
    use SoftDeletes;

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

    protected $message = '';

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

    public function generate($mainDomain, $additionalDomains = "")
    {
        $addonDomainsAuthorize = null;

        if (!empty($additionalDomains)) {
            $additionalDomainsArray = explode(",", $additionalDomains);

            // additional domains used during authorization
            $addonDomainsAuthorize = implode(" ", $additionalDomainsArray); // (domaina.com domainb.com)

            $convertedDomainsArray = [];

            foreach ($additionalDomainsArray as $value) {
                array_push($convertedDomainsArray, "-a " . trim($value));
            }

            // additional domains used during the request
            $additionalDomains = implode(" ", $convertedDomainsArray); // (-a domaina.com -a domainb.com)
        }
        
        $authResponse = $this->certificateAuthorization($mainDomain, $addonDomainsAuthorize);
        
        $this->certificateChallenge($authResponse);

        $certificateRequestCheck = $this->certificateRequestCheck($mainDomain, $addonDomainsAuthorize);

        // Note: only uncomment this when needed because it generates an actual certificate
        $certificateRequest = $this->certificateRequest($mainDomain, $additionalDomains);

        if (!empty($additionalDomains)) return $additionalDomainsArray;
        return $this->message;
    }

    public function certificateAuthorization($mainDomain, $additionalDomains = "")
    {
        $authResponse = shell_exec(env('ROOT_DIR') . "authorize {$mainDomain} {$additionalDomains} -n");
        $successMessage = "The authorization tokens was successfully fetched!";
        
        if (Str::contains($authResponse, $successMessage)) {
            return "success";

        } else {
            return redirect('/create-certificate')->with('error', "Certificate authorization error");
        }
    }

    public function certificateChallenge($authResponse)
    {
        $matches = [];
        preg_match_all('/{(.*?)}}/', $authResponse, $matches);

        foreach ($matches[0] as $match) {

            $data = json_decode($match);

            $curl = curl_init();

            curl_setopt_array($curl, array(
                CURLOPT_URL => 'http://' . $data->domain . '/letsencrypt/token?token=' . $data->challenge->token . '&payload=' . $data->challenge->payload,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
            ));

            $response = curl_exec($curl);
            $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    
            curl_close($curl);

            if ($http_code != "200") {
                return redirect('/create-certificate')->with('error', "Failed to load the challenge for ". $data->domain);
            } 
           
        }
    }

    public function certificateRequestCheck($mainDomain, $additionalDomains = "")
    {
        $check = shell_exec(env('ROOT_DIR') . "check -s http {$mainDomain} {$additionalDomains}");
        $successMessage = 'The authorization check was successful!';

        if (Str::contains($check, $successMessage)) {
            $this->message = "success";
            return $this->message;
        } else {
            return redirect('/create-certificate')->with('error', "Request challenge did not pass");
        }
    }

    public function certificateRequest($mainDomain, $additionalDomains)
    {
        $request = shell_exec(env('ROOT_DIR') . "request {$mainDomain} {$additionalDomains}");
        $successMessage = "The SSL certificate was fetched successfully!";

        if (Str::contains($request, $successMessage)) {
            $this->message = "success";
            return $this->message;
        } else {
            return redirect('/create-certificate')->with('error', "Failed to fetch the certificate");
        }
    }

    public function domains()
    {
        return $this->hasMany(Domains::class);
    }
}
