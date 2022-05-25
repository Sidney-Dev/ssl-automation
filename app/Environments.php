<?php

namespace App;
//require_once('../vendor/autoload.php');
use GuzzleHttp\Client;
use App\Models\LetsEncryptCertificate;

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
        $cert = urlencode(file_get_contents(storage_path('app/letsencrypt/certificates/' . $domain . '/cert.pem')));
        $chain = urlencode(file_get_contents(storage_path('app/letsencrypt/certificates/' . $domain . '/chain.pem')));
        $key = urlencode(file_get_contents(storage_path('app/letsencrypt/certificates/' . $domain . '/privkey.pem')));

        try {
            $curl = curl_init();

            curl_setopt_array($curl, array(
                CURLOPT_URL => 'cloud.acquia.com/api/environments/' . $envID . '/ssl/certificates',
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
            $httpcode = curl_getinfo($curl,CURLINFO_HTTP_CODE);
            curl_close($curl);

            $certificateID = $this->getCertificateID($certName, $envID);
            LetsEncryptCertificate::where('domain',$domain)->update(['slug' => $certificateID, 'environmentID' => $envID]); // update LetsEncryptCertificate table
          
            if ($httpcode == '202') {
                return true;
            }

        } catch(\Exception $e) {
            dd($e->getMessage());
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
                return true;
            }

        } catch (\Exception $e) {
            dd($e->getMessage());
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
                return true;
            }

        } catch (\Exception $e) {
            dd($e->getMessage());
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
                return true;
            }

        } catch (\Exception $e) {
            dd($e->getMessage());
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
            dd($e->getMessage());
        }
    }

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


//$domains = new Domains();
//
//$domains->updateDNS('value', 'RC8Tg2MUzuBS3c7oy6m8IJWZJS4cZ3XEf48XJ20hmvI');
