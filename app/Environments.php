<?php

namespace App;
//require_once('../vendor/autoload.php');
use GuzzleHttp\Client;
use App\Models\LetsEncryptCertificate;
use App\Exceptions\CertificateNotActivatedException;
use App\Exceptions\CertificateNotInstalledException;
use App\Exceptions\CertificateNotDeactivatedException;
use App\Exceptions\CertificateNotDeletedException;
use App\Exceptions\EnvironmentNotFoundException;
use App\Exceptions\ClearDomainCacheException;

class Environments
{
    protected $accessTokenUrl = "https://accounts.acquia.com/api/auth/oauth/token";
    protected $clientId = 'e6bc5dd0-78fd-4055-bed8-671cb8b44013';
    protected $secretId = 'kr90tBE1naG6WBej6DW/WANeVd8vSVSKA0zTOsQ7TmM=';
    protected $authenticationToken;

    public function __construct()
    {
        try {
            $this->authenticationToken = '';
            $token = (new client())->post($this->accessTokenUrl, [
                'form_params' => [
                    'client_id' => $this->clientId,
                    'client_secret' => $this->secretId,
                    'grant_type' => 'client_credentials'
                ]
            ])->getBody()->getContents();

            $this->authenticationToken = json_decode($token)->access_token;
        } catch (\Exception $e) {
            dd($e->getMessage());
        }
    }

    public function addCertificateToEnvironment($certName, $envID, $domain)
    {
        
        $cert = urlencode(file_get_contents(env('MAIN_DIR').'/.acmephp/master/certs/'. $domain .'/public/cert.pem'));
        $chain = urlencode(file_get_contents(env('MAIN_DIR').'/.acmephp/master/certs/'. $domain . '/public/chain.pem'));
        $key = urlencode(file_get_contents(env('MAIN_DIR').'/.acmephp/master/certs/'. $domain . '/private/key.private.pem'));
    
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://cloud.acquia.com/api/environments/' . $envID . '/ssl/certificates',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => "legacy=0&certificate=" . $cert . "&private_key=" . $key . "&ca_certificates=" . $chain . "&label=" . $certName,
            CURLOPT_HTTPHEADER => array(
                'Authorization: Bearer ' . $this->authenticationToken,
                'Content-Type: application/x-www-form-urlencoded'
            ),
        ));

        $response = curl_exec($curl);
        curl_close($curl);
      
        if (strpos($response,"error") === false) {
            $certificateID = $this->getCertificateID($certName, $envID);
            LetsEncryptCertificate::where('domain', $domain)->update(['slug' => $certificateID, 'environmentID' => $envID, 'status' => 'installed', 'label' => $certName]); // update LetsEncryptCertificate table
            return true;
        } else {
            throw new CertificateNotInstalledException();
        }
    }


    public function certificateActivation($envID, $certificateID)
    {
        try {
            $client = new Client();
            $headers = [
                'Authorization' => 'Bearer ' . $this->authenticationToken,
                'cache-control' => 'no-cache',
            ];
            $response = $client->request('POST', 'https://cloud.acquia.com/api/environments/' . $envID . '/ssl/certificates/' . $certificateID . '/actions/activate', [
                'headers' => $headers
            ]);

            $httpcode = $response->getStatusCode();

            if ($httpcode == "202") {
                LetsEncryptCertificate::where('slug', $certificateID)->update(['status' => 'activated']); // update LetsEncryptCertificate table
                return true;
            }

        } catch (\Exception $e) {
            throw new CertificateNotActivatedException();
        }
    }

    public function clearDomainCache($envID,$certificateID)
    {
        $domains = LetsEncryptCertificate::where('slug',$certificateID)->with('domains')->first();
        $allDomains[] = $domains->domain;
  
        foreach ($domains->domains as $domain) {
            array_push($allDomains,$domain->name);
        }

        $curl = curl_init();

        $post = [
            'domains' => $allDomains
        ];
        
        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://cloud.acquia.com/api/environments/' . $envID . '/actions/clear-caches',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($post),
            CURLOPT_HTTPHEADER => array(
                'Authorization: Bearer ' . $this->authenticationToken,
                'Content-Type: application/json'
            ),
        ));

        $response = curl_exec($curl);
        curl_close($curl);

        if (strpos($response,"error") === false) {
            return true;
        } else {
            throw new ClearDomainCacheException();
        } 
    }

    public function certificateDeactivation($envID, $certificateID)
    {
        try {
            $client = new Client();
            $headers = [
                'Authorization' => 'Bearer ' . $this->authenticationToken,
                'cache-control' => 'no-cache',
            ];
            $response = $client->request('POST', 'https://cloud.acquia.com/api/environments/' . $envID . '/ssl/certificates/' . $certificateID . '/actions/deactivate', [
                'headers' => $headers
            ]);

            $httpcode = $response->getStatusCode();
            if ($httpcode == "202") {
                LetsEncryptCertificate::where('slug', $certificateID)->update(['status' => 'deactivated']);
                return true;
            }

        } catch (\Exception $e) {
            throw new CertificateNotDeactivatedException();
        }
    }

    public function certificateDeletion($envID, $certificateID)
    {
        try {
            $client = new Client();
            $headers = [
                'Authorization' => 'Bearer ' . $this->authenticationToken,
                'cache-control' => 'no-cache',
            ];
            $response = $client->request('DELETE', 'https://cloud.acquia.com/api/environments/' . $envID . '/ssl/certificates/' . $certificateID, [
                'headers' => $headers
            ]);

            $httpcode = $response->getStatusCode();
            if ($httpcode == "202") {
                LetsEncryptCertificate::where('slug', $certificateID)->update(['status' => 'pending']);
                return true;
            }

        } catch (\Exception $e) {
            throw new CertificateNotDeletedException();
        }
    }

    public function getEnvironments()
    {
        try {
            $client = new Client();
            $headers = [
                'Authorization' => 'Bearer ' . $this->authenticationToken,
                'cache-control' => 'no-cache'
            ];
            $response = $client->request('GET', 'https://cloud.acquia.com/api/applications/df6a4756-6dcc-4190-90b9-d7b3e870a6c2/environments', [
                'headers' => $headers
            ]);

            $httpcode = $response->getStatusCode();
            $environments = json_decode($response->getBody()->getContents());

            if ($httpcode == "200") {
                return $environments->_embedded->items;
            }
        } catch (\Exception $e) {
            throw new EnvironmentNotFoundException();
        }
    }

    // TODO: rather send the 
    public function getCertificateID($certName,$envID)
    {
        try {
            $client = new Client();
            $headers = [
                'Authorization' => 'Bearer ' . $this->authenticationToken,
                'cache-control' => 'no-cache',
                'content-type'  => 'application/x-www-form-urlencoded'
            ];
            $response = $client->request('GET', 'https://cloud.acquia.com/api/environments/'.$envID.'/ssl/certificates', [
                'headers' => $headers
            ]);

            $httpcode = $response->getStatusCode();
            $response = json_decode($response->getBody()->getContents());

            if ($httpcode == "200") {
                $certs = $response->_embedded->items;
                foreach ($certs as $cert) {
                    if ($cert->label == $certName) {
                        return $cert->id;
                    }
                }
            }
        } catch (\Exception $e) {
            dd($e->getMessage());
        }
    }

    public function __destruct()
    {
    }
}
