<?php

namespace App\Interfaces;

interface LetsEncryptCertificateFailed
{
    public function getException(): \Throwable;
}
