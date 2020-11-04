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
     * @return array
     */
    private function getResponse($response)
    {
        $result = [
            'status' => $response->ok(),
            'statusCode' => $response->status(),
            'data' => $response->object(),
            'message' => 'Ok'
        ];
        if ($response->failed()) {
            $result['message'] = 'HTTP Request: ' . $response->toPsrResponse()->getReasonPhrase();
        }

        return $result;
    }
}
