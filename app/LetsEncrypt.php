<?php

namespace App;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;
use App\Models\LetsEncryptCertificate;
use App\Exceptions\DomainAlreadyExists;
use App\Exceptions\InvalidDomainException;

/**
 * TODO: check the certificate states
 * A certificate should only have three states:
 * 1 - pending: it means that the certificate is not yet in an acquia environment
 * 2 - inactive: it means that the certificate is installed in an environment but not inactive
 * 3 - active: the certificate is actively installed
 */

class LetsEncrypt
{
    public $certificateValidationDate = null;

    public $error = false;
    public $errorMessage = null;


    public function generate($mainDomain, $additionalDomains = "")
    {
        $addonDomainsAuthorize = null;

        if (!empty($additionalDomains)) {
            
            $additionalDomainsArray = explode("\r\n", trim($additionalDomains));
            
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

        $this->certificateRequestCheck($mainDomain, $addonDomainsAuthorize);

        // Note: only uncomment this when needed because it generates an actual certificate
        $this->certificateRequest($mainDomain, $additionalDomains);
        
        // check if all the files have been generated and fetched
        $this->checkGeneratedCertificateDir($mainDomain); 

        if (!empty($additionalDomains)) return $additionalDomainsArray;
        return true;
    }

    public function certificateAuthorization($mainDomain, $additionalDomains = "")
    {
        $authResponse = shell_exec(env('ROOT_DIR') . "authorize {$mainDomain} {$additionalDomains} -n");
      
        $successMessage = "The authorization tokens was successfully fetched!";

        if (Str::contains($authResponse, $successMessage)) {
            return $authResponse;
        } else {
            $this->error = true;
            $this->errorMessage .= "Failed to fetch the authorization token.".PHP_EOL;
        }
    }

    public function certificateChallenge($authResponse): void
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
                $this->error = true;
                $this->errorMessage .= "Failed to upload the challenge on the server.".PHP_EOL;
            }
        }
    }

    public function certificateRequestCheck($mainDomain, $additionalDomains = "")
    {
        $check = shell_exec(env('ROOT_DIR') . "check -s http {$mainDomain} {$additionalDomains}");
        $successMessage = 'The authorization check was successful!';

        if (!Str::contains($check, $successMessage)) {
            $this->error = true;
            $this->errorMessage .= "The authorization check failed.".PHP_EOL;
        } 
    }

    public function certificateRequest($mainDomain, $additionalDomains)
    {
        $request = shell_exec(env('ROOT_DIR') . "request {$mainDomain} {$additionalDomains}");

        $fetched = "The SSL certificate was fetched successfully!";
        $renewed = "Current certificate is valid";

        if (!Str::contains($request, $fetched || !Str::contains($request, $renewed))) {
            $this->error = true;
            $this->errorMessage .= "Failed to fetch the certificate.".PHP_EOL;
        } 

        $this->setCertificateValidationDate($request);
    }

    /**
     * Extracts the certificate validation date from the lets encrypt server response.
     * @param $response
     * @return void
     */

    public function setCertificateValidationDate($response): void
    {
        preg_match('/(\d{4})-(\d{2})-(\d{2})T(\d{2}):(\d{2}):(\d{2})/', $response, $validation_date);
        $this->certificateValidationDate = date('Y-m-d H:i:s', strtotime(current($validation_date)));
    }

    public function checkGeneratedCertificateDir($domain) {
        $files['private'] = ['combined.pem','key.private.pem','key.public.pem'];
        $files['public'] = ['cert.pem','chain.pem','fullchain.pem'];

        $path = env('MAIN_DIR') . '/.acmephp/master/certs/' . $domain.'/';

        $is_file_exists = false;

        foreach($files['private'] as $filename) {
            $fullpath = $path.'private/'.$filename;

            if (!File::exists($fullpath)) {
                $is_file_exists = true;
                $this->error = true;
                $this->errorMessage .= $fullpath.' does not exist.'.PHP_EOL;
                break;
            }
        }

        if ($is_file_exists === false) {
            foreach($files['public'] as $filename) {
                $fullpath = $path.'public/'.$filename;
    
                if (!File::exists($fullpath)) {
                    $is_file_exists = true;
                    $this->error = true;
                    $this->errorMessage .= $fullpath.' does not exist.'.PHP_EOL;
                    break;
                }
            }
        }
    }

    public function renew($certificate, $subdomains) {

        try{

            $env = new Environments;

            $env->certificateDeactivation($certificate->environmentID, $certificate->slug);
            $env->certificateDeletion($certificate->environmentID, $certificate->slug);
    
            $this->removeDir($certificate->domain);
            $this->generate($certificate->domain, $subdomains);
    
            $addCertificateToEnvironment = $env->addCertificateToEnvironment($certificate->label, $certificate->environmentID, $certificate->domain);
               
            if($addCertificateToEnvironment) {
    
                $updatedCertificate = LetsEncryptCertificate::where('domain', $certificate->domain)->first(); //go grab the updated slug by quering letsencryptcertificate model
                $certificateActivation = $env->certificateActivation($updatedCertificate->environmentID, $updatedCertificate->slug);
                
            }
        } catch(\Exception $e){

        }
    }

     /**
     * Rename ssl certificate directory
     * @param string $domain
     * @param string $suffix
     * @return void
     */

    public function renameDir($domain,$suffix)
    {
        $path = realpath(env('MAIN_DIR') . '/.acmephp/master/certs/' . $domain);

        if (File::exists($path)) {
        
            $newPath = $path.$suffix;
            exec("mv $path $newPath");

        } else {

            $path = realpath(env('MAIN_DIR') . '/.acmephp/master/certs/' . $domain.$suffix);
            $replace = str_replace($suffix,"",$path);

            if (File::exists($path)) {
                exec("mv $path $replace");
            }
        }
    }
    
    public function removeDir($domain)
    {
        $path = realpath(env('MAIN_DIR') . '/.acmephp/master/certs/' . $domain);

        if (File::exists($path)) {
            File::deleteDirectory($path);
        }
    }


    /**
     * Checks mainly to prevent API errors when a user passes e.g. 'https://domain.com' as a domain. This should be
     * 'domain.com' instead.
     * @param string $domain
     * @throws InvalidDomainException
     */
    public function validateDomain(string $domain): void
    {
        if (Str::contains($domain, [':', '/', ','])) {
            throw new InvalidDomainException($domain);
        }
    }

    /**
     * @param string $domain
     * @throws DomainAlreadyExists 
     */
    public function checkDomainDoesNotExist(string $domain): void
    {
        if (LetsEncryptCertificate::where('domain', $domain)->exists()) {
            throw new DomainAlreadyExists($domain);
        }
    }
}



