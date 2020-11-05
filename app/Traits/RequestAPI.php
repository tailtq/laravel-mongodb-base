<?php

namespace App\Traits;

use Illuminate\Support\Facades\Http;

trait RequestAPI
{
    public function sendGETRequest($url, $params = [], $headers = [])
    {
        $response = Http::withHeaders($headers)->get($url, $params);

        return $this->getResponse($response);
    }

    public function sendPOSTRequest($url, $body, $headers = [])
    {
        $response = Http::withHeaders($headers)->post($url, $body);

        return $this->getResponse($response);
    }

    public function sendPUTRequest($url, $body, $headers = [])
    {
        $response = Http::withHeaders($headers)->put($url, $body);

        return $this->getResponse($response);
    }

    public function sendDELETERequest($url, $body = [], $headers = [])
    {
        $response = Http::withHeaders($headers)->delete($url, $body);

        return $this->getResponse($response);
    }

    /**
     * @param \Illuminate\Http\Client\Response $response
     * @return \stdClass
     */
    private function getResponse($response)
    {
        $result = new \stdClass();
        $result->status = $response->ok();
        $result->statusCode = $response->status();
        $result->message = 'Ok';
        $result->body = $response->object();

        if ($response->failed()) {
            $result->message = 'HTTP Request: ' . $response->toPsrResponse()->getReasonPhrase();
        }

        return $result;
    }
}
