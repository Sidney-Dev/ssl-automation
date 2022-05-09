<?php

namespace App\Jobs;

use App\Collections\LetsEncryptCertificateCollection;
use App\Events\RenewExpiringCertificatesFailed;
use App\Models\LetsEncryptCertificate;
use App\Traits\Retryable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class RenewExpiringCertificates implements ShouldQueue
{
    use Dispatchable, Queueable, InteractsWithQueue, SerializesModels, Retryable;

    public function __construct(int $tries = null, int $retryAfter = null, $retryList = [])
    {
        $this->tries = $tries;
        $this->retryAfter = $retryAfter;
        $this->retryList = $retryList;
    }

    public function handle()
    {
        LetsEncryptCertificate::query()
            ->requiresRenewal()
            ->chunk(100, function (LetsEncryptCertificateCollection $certificates) {
                $certificates->renew();
            });
    }

    /**
     * Handle a job failure.
     *
     * @return void
     */
    public function failed(\Throwable $exception)
    {
        event(new RenewExpiringCertificatesFailed($exception));
    }
}
