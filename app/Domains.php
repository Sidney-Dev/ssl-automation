<?php

namespace App;
//require_once('../vendor/autoload.php');
use GuzzleHttp\Client;

class Domains
{
    /**
     * @var Client
     */
    private $client;

    public function __construct()
    {
        $this->client = new Client();
    }

    public function updateDNS($key, $value, $name = "_acme-challenge")
    {
        $response = $this->client->put('https://api.123domain.eu/api/v1/domains/de/bcm-dev02/dns/ZySefnS_l4L83V3M78NO9Q', [
            'headers' => [
                'Authorization' => 'Bearer sy27yC3H2rQtDTcQNX8M:eCFgWybcOI4RbGWcWRfN54dchWA9gka6PWKv8wop',
                'Accept' => 'application/json'
            ],
            'json' => [
                "name" => $name,
                "type" => "TXT",
                "ttl" => 60,
                "comment" => "Lets Encrypt",
                "attributes" => [
                    [
                        "key" => $key,
                        "value" => $value
                    ]
                ]
            ]
        ]);

        echo $response->getBody();
    }

}


//$domains = new Domains();
//
//$domains->updateDNS('value', 'RC8Tg2MUzuBS3c7oy6m8IJWZJS4cZ3XEf48XJ20hmvI');
