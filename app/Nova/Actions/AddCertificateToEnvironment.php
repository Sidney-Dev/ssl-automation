<?php

namespace App\Nova\Actions;

use GuzzleHttp\Client;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;

class AddCertificateToEnvironment extends Action
{
    use InteractsWithQueue, Queueable;

    public $name = "Add to environment";

    /**
     * Perform the action on the given models.
     *
     * @param \Laravel\Nova\Fields\ActionFields $fields
     * @param \Illuminate\Support\Collection $models
     * @return mixed
     */
    public function handle(ActionFields $fields, Collection $models)
    {
        // Get token from acquia
        $token = (new Client())->post('https://accounts.acquia.com/api/auth/oauth/token', [
            'form_params' => [
                "client_id" => "14d6bbb4-ffb2-4e17-91b9-693ae2d4a449",
                "client_secret" => "9DXAzVWneY+JML/QmirDZSLm5LDAekuOqOQTO/YVf4Q=",
                "grant_type" => "client_credentials"
            ]
        ])->getBody()->getContents();
        // Post the Cert to acquia
        $cert = file_get_contents(storage_path('app/letsencrypt/certificates/cohesiondx8s5max7g9qc.devcloud.acquia-sites.com/cert.pem'));
        $fullchain = str_replace($cert, '',file_get_contents(storage_path('app/letsencrypt/certificates/cohesiondx8s5max7g9qc.devcloud.acquia-sites.com/fullchain.pem')));

        $ssl = (new Client())->post('https://cloud.acquia.com/api/environments/' . $fields->environment .'/ssl/certificates', [
            'headers' => [
              'Authorization' => 'Bearer ' . json_decode($token, false)->access_token,
              'Content-Type' => 'application/x-www-form-urlencoded'
            ],
            'form_params' => [
                "legacy" => 0,
                "certificate" => $cert,
                "private_key" => file_get_contents(storage_path('app/letsencrypt/certificates/cohesiondx8s5max7g9qc.devcloud.acquia-sites.com/privkey.pem')),
                "ca_certificates" => $fullchain,
//                "csr_id" => 12345,
                "label" => $fields->name
            ]
        ]);


//        $curl = curl_init();
//
//        curl_setopt_array($curl, array(
//            CURLOPT_URL => 'cloud.acquia.com/api/environments/315962-535a0a5e-1fd6-4fb7-8e85-739e405c0ba4/ssl/certificates',
//            CURLOPT_RETURNTRANSFER => true,
//            CURLOPT_ENCODING => '',
//            CURLOPT_MAXREDIRS => 10,
//            CURLOPT_TIMEOUT => 0,
//            CURLOPT_FOLLOWLOCATION => true,
//            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
//            CURLOPT_CUSTOMREQUEST => 'POST',
//            CURLOPT_POSTFIELDS => "label=LetsEncrypt&certificate=" . file_get_contents(storage_path('app/letsencrypt/certificates/cohesiondx8s5max7g9qc.devcloud.acquia-sites.com/cert.pem')) . "&private_key=" . file_get_contents(storage_path('app/letsencrypt/certificates/cohesiondx8s5max7g9qc.devcloud.acquia-sites.com/privkey.pem')) . "&ca_certificates=" . file_get_contents(storage_path('app/letsencrypt/certificates/cohesiondx8s5max7g9qc.devcloud.acquia-sites.com/chain.pem')),
//            CURLOPT_HTTPHEADER => array(
//                'Authorization: Bearer ' . json_decode($token, false)->access_token,
//                'Content-Type: application/x-www-form-urlencoded'
//            ),
//        ));
//
//        $response = curl_exec($curl);
//
//        curl_close($curl);


//        list($header, $body) = explode("\r\n\r\n", $response, 2);
//        if ($ssl->getStatusCode() === 200) {
            return Action::message($ssl->getBody()->getContents());
//        }

//        return Action::danger($ssl->getBody()->getContents());
    }

    /**
     * Get the fields available on the action.
     *
     * @param \Laravel\Nova\Http\Requests\NovaRequest $request
     * @return array
     */
    public function fields(NovaRequest $request)
    {
        return [
            Text::make('name'),
            Text::make('environment')
        ];
    }
}
