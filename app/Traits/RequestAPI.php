<?php

namespace App\Traits;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException;

trait RequestAPI
{
    public function getResponseAPI() //Retrieve
    {
        $client = new Client();
        $response = $client->get('http://api.example.com/v1/retrieve');
        $body = json_decode($response->getBody(), TRUE);
        return $body;
    }

    public function postResponseAPI() //Retrieve
    {
        $client = new Client();
        $response = $client->post('http://api.example.com/v1/retrieve');
        $body = json_decode($response->getBody(), TRUE);
        return $body;
    }
}
