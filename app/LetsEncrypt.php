<?php

namespace App;

use Illuminate\Support\Str;

class LetsEncrypt
{
    public $certificateValidationDate = null;

    protected $message = '';
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

        if (!empty($additionalDomains)) return $additionalDomainsArray;
        return $this->message;
    }

    public function certificateAuthorization($mainDomain, $additionalDomains = "")
    {
        $authResponse = shell_exec(env('ROOT_DIR') . "authorize {$mainDomain} {$additionalDomains} -n");
        $successMessage = "The authorization tokens was successfully fetched!";

        if (Str::contains($authResponse, $successMessage)) {
            return $authResponse;
        } else {
            $this->error = true;
            $this->errorMessage = "Failed to fetch the authorization token";
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
                $this->errorMessage = "Failed to upload the challenge on the server";
            }
        }
    }

    public function certificateRequestCheck($mainDomain, $additionalDomains = "")
    {
        $check = shell_exec(env('ROOT_DIR') . "check -s http {$mainDomain} {$additionalDomains}");
        $successMessage = 'The authorization check was successful!';

        if (Str::contains($check, $successMessage)) {
            $this->message = "success";
            return $this->message;
        } else {
            $this->error = true;
            $this->errorMessage = "The authorization check failed";
        }
    }

    public function certificateRequest($mainDomain, $additionalDomains)
    {
        $request = shell_exec(env('ROOT_DIR') . "request {$mainDomain} {$additionalDomains}");
        
        $successMessage = "The SSL certificate was fetched successfully!";

        if (Str::contains($request, $successMessage)) {
            $this->setCertificateValidationDate($request);
            $this->message = "success";
            return $this->message;
        } else {
            $this->error = true;
            $this->errorMessage = "Failed to fetch the certificate";
        }
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
}
