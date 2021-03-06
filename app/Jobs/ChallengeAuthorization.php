<?php

namespace App\Jobs;

use AcmePhp\Core\Protocol\AuthorizationChallenge;
use App\Events\ChallengeAuthorizationFailed;
use App\Facades\LetsEncrypt;
use App\Traits\Retryable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ChallengeAuthorization implements ShouldQueue
{
    use Dispatchable, Queueable, InteractsWithQueue, SerializesModels, Retryable;

    /**
     * @var AuthorizationChallenge
     */
    protected $challenge;


    public function __construct($httpChallenge, int $tries = null, int $retryAfter = null, array $retryList = [])
    {
        $this->challenge = $httpChallenge;
        $this->tries = $tries;
        $this->retryAfter = $retryAfter;
        $this->retryList = $retryList;
    }

    /**
     * Tells the LetsEncrypt API that our challenge is in place. LetsEncrypt will attempt to access
     * the challenge on <domain>/.well-known/acme-challenges/<token>
     * If this job succeeds, we can clean up the challenge and request a certificate.
     * @throws \App\Exceptions\InvalidKeyPairConfiguration
     */
    public function handle()
    {
        $client = LetsEncrypt::createClient();
        $client->challengeAuthorization($this->challenge);
        CleanUpChallenge::dispatch($this->challenge, $this->tries, $this->retryAfter, $this->retryList);
    }

    /**
     * Handle a job failure.
     *
     * @return void
     */
    public function failed(\Throwable $exception)
    {
        event(new ChallengeAuthorizationFailed($exception));
    }
}
