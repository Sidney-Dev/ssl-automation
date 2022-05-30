<?php

namespace App\Models;

use App\Builders\LetsEncryptCertificateBuilder;
use App\Collections\LetsEncryptCertificateCollection;
use App\Facades\LetsEncrypt;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

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

    public function generate($mainDomain) {

    
        try {
    
            $authResponse = $this->certificateAuthorization($mainDomain);
            $this->certificateChallenge($authResponse);
            
            // $certificateRequestCheck = $this->certificateRequestCheck($mainDomain);

            // $certificateRequest = $this->certificateRequest($mainDomain);
    
            // dd($authResponse);

        } catch(\Exception $e) {
    
            var_dump($e->getMessage());
    
        }
    }

    public function certificateAuthorization($mainDomain) {

        $authResponse = shell_exec(env('ROOT_DIR') . "authorize {$mainDomain} -n");
        $successMessage = "The authorization tokens was successfully fetched!";

        // if(Str::contains($authResponse, $successMessage)){
        //     return true;
        // }
        
        return $authResponse;
    }

    public function certificateChallenge($authResponse) {

        $matches = [];
        preg_match_all('/{(.*?)}}/', $authResponse, $matches);

        foreach($matches[0] as $match) {

            $data = json_decode($match);

            $curl = curl_init();

            curl_setopt_array($curl, array(
                CURLOPT_URL => 'http://'. $data->domain .'/letsencrypt/token?token=' . $data->challenge->token . '&payload=' . $data->challenge->payload,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
            ));

            $response = curl_exec($curl);

            curl_close($curl);
        }
    }

    public function certificateRequestCheck($mainDomain) {

        $check = shell_exec(env('ROOT_DIR') . "check {$mainDomain}");
        $successMessage = 'The authorization check was successful!';

        // if(Str::contains($check, $successMessage)){
        //     dd($successMessage);
        // }
        return $check;
    }

    public function certificateRequest($mainDomain) {

        try {

            $request = shell_exec(env('ROOT_DIR') . "request {$mainDomain}");
            return $request;

        } catch(\Exception $e) {
    
            var_dump($e->getMessage());
    
        }
    }
    
    public function domains()
    {
        return $this->hasMany(Domains::class);
    }
}
