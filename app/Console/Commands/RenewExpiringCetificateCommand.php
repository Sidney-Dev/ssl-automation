<?php

namespace App\Console\Commands;

use App\Collections\LetsEncryptCertificateCollection;
use App\Models\LetsEncryptCertificate;
use Illuminate\Console\Command;

class RenewExpiringCetificateCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'lets-encrypt:renew';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Renew an expired SSL certificate through Let\'s Encrypt.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        LetsEncryptCertificate::query()
        ->requiresRenewal()
        ->chunk(100, function (LetsEncryptCertificateCollection $certificates) {
            $certificates->renew();
        });
    }
}
