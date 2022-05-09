<?php

namespace App\Events;

use App\Interfaces\LetsEncryptCertificateFailed;
use App\Models\LetsEncryptCertificate;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class StoreCertificateFailed implements LetsEncryptCertificateFailed
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /** @var \Throwable */
    protected $exception;

    /** @var LetsEncryptCertificate */
    protected $certificate;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(\Throwable $exception, LetsEncryptCertificate $certificate)
    {
        $this->exception = $exception;
        $this->certificate = $certificate;
    }

    public function getException(): \Throwable
    {
        return $this->exception;
    }

    public function getCertificate(): LetsEncryptCertificate
    {
        return $this->certificate;
    }
}
