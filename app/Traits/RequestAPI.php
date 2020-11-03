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
        $response = $client->get('https://5fa119bf2541640016b6a6d8.mockapi.io/Tet');
        $body = json_decode($response->getBody(), true);
        dd($body);
        return $body;
    }

    public function postResponseAPI()
    {
        $client = new Client();
        $response = $client->post('http://api.example.com/v1/retrieve');
        $body = json_decode($response->getBody(), true);
        return $body;
    }
}
