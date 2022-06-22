<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\LetsEncryptCertificate;
use App\LetsEncrypt;
use App\Environments;
use App\Mail\LetsencryptEmail;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

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
        $user = User::take(1)->first();
      
        foreach($certificates as $certificate) {
            
            if ($currentTimestamp > $certificate->certificate_validation_date) {
                $env = new Environments();
                $letsencrypt = new LetsEncrypt();

                $subdomains = null;
                foreach($certificate->domains as $subdomain) {
                    $subdomains .= $subdomain->name . "\r\n";
                }
                
                $letsencrypt->renameDir($certificate->domain, "-old");
                $letsencrypt->renew($certificate, $subdomains);

                if ($letsencrypt->error == false) {
                    $env->certificateDeactivation($certificate->environmentID, $certificate->slug);
                    $env->certificateDeletion($certificate->environmentID, $certificate->slug);

                    $env->addCertificateToEnvironment($certificate->label, $certificate->environmentID, $certificate->domain);
                    $updatedCertificate = LetsEncryptCertificate::where('domain', $certificate->domain)->first(); //go grab the updated slug by quering letsencryptcertificate model
                    $env->certificateActivation($updatedCertificate->environmentID, $updatedCertificate->slug);

                    $letsencrypt->removeDir($certificate->domain . "-old");
                } else {
                    $letsencrypt->removeDir($certificate->domain);
                    $letsencrypt->renameDir($certificate->domain, "-old");

                    $mailData = [
                        "name" => $user->name,
                        "domain" => $certificate->domain,
                        "errorMessage" => $letsencrypt->errorMessage,
                    ];

                    Mail::to($user->email)->send(new LetsencryptEmail($mailData));
                }
            }
        }

        // TODO: email notification
        // echo "renewd";
        return 0;
    }
}
