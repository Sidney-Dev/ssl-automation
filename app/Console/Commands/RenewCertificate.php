<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\LetsEncryptCertificate;
use App\LetsEncrypt;

class RenewCertificate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cert:renew';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Renews an ssl certificate';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {

        /**
         * On a daily basis
         * get current timestamp
         * Loop through the certificates table
         * Check if the certificate_validation_date is older than current timestamp
         * For every success result, get the certificate domain, as well as the subdomains
         * Call the generate certificate method
         */

        $currentTimestamp = date("Y-m-d H:i:s");
        $certificates = LetsEncryptCertificate::with('domains')->get();
    
        foreach($certificates as $certificate) {
            
            if ($currentTimestamp > $certificate->certificate_validation_date) {
                
                $subdomains = null;
                foreach($certificate->domains as $subdomain) {
                    $subdomains .= $subdomain->name . "\r\n";
                }
                
                (new LetsEncrypt)->renew($certificate, $subdomains);
    
            }
        }

        // TODO: email notification
        echo "renewd";
        return 0;
    }
}
