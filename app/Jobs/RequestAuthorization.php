<?php

namespace App\Jobs;

use AcmePhp\Core\Protocol\AuthorizationChallenge;
use App\Events\RequestAuthorizationFailed;
use App\Exceptions\FailedToMoveChallengeException;
use App\Facades\LetsEncrypt;
use App\Models\LetsEncryptCertificate;
use App\Support\PathGeneratorFactory;
use App\Traits\Retryable;
use GuzzleHttp\Client;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Bus\Dispatcher;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class RequestAuthorization implements ShouldQueue
{
    use Dispatchable, Queueable, InteractsWithQueue, SerializesModels, Retryable;

    /** @var LetsEncryptCertificate */
    protected $certificate;

    /** @var bool */
    protected $sync;


    public function __construct(LetsEncryptCertificate $certificate, int $tries = null, int $retryAfter = null, $retryList = [])
    {
        $this->sync = false;
        $this->certificate = $certificate;
        $this->tries = $tries;
        $this->retryAfter = $retryAfter;
        $this->retryList = $retryList;
    }

    /**
     * Out of the array of challenges we have, we want to find the HTTP challenge, because that's the
     * easiest one to solve in this scenario.
     * @param AuthorizationChallenge[] $challenges
     * @return AuthorizationChallenge
     */
    protected function getHttpChallenge(array $challenges): AuthorizationChallenge
    {
        return collect($challenges)->first(function (AuthorizationChallenge $challenge): bool {
            return Str::startsWith($challenge->getType(), 'http');
        });
    }

    /**
     * Stores the HTTP-01 challenge at the appropriate place on disk.
     * @param AuthorizationChallenge $challenge
     * @throws FailedToMoveChallengeException
     */
    protected function placeChallenge(AuthorizationChallenge $challenge): void
    {

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'http://'.$challenge->getDomain().'/letsencrypt/token?token=' . $challenge->getToken() . '&payload=' . $challenge->getPayload(),
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
        // dd([$response, 'http://'.$challenge->getDomain().'/letsencrypt/token?token=' . $challenge->getToken() . '&payload=' . $challenge->getPayload()]);
    }

    public function handle()
    {
        $client = LetsEncrypt::createClient();
        $challenges = $client->requestAuthorization($this->certificate->domain);
        $httpChallenge = $this->getHttpChallenge($challenges);
        $this->placeChallenge($httpChallenge);

        if ($this->sync) {
            ChallengeAuthorization::dispatchNow($httpChallenge, $this->tries, $this->retryAfter, $this->retryList);
        } else {
            ChallengeAuthorization::dispatch($httpChallenge, $this->tries, $this->retryAfter, $this->retryList);
        }
    }

    protected function setSync(bool $sync)
    {
        $this->sync = $sync;
    }

    public static function dispatchNow(LetsEncryptCertificate $certificate)
    {
        $job = new static($certificate);
        $job->setSync(true);
        app(Dispatcher::class)->dispatchNow($job);
    }

    /**
     * Handle a job failure.
     *
     * @return void
     */
    public function failed(\Throwable $exception)
    {
        event(new RequestAuthorizationFailed($exception, $this->certificate));
    }
}
