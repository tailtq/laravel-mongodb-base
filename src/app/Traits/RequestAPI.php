<?php

namespace App\Traits;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

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
        $result->status = $response->successful();
        $result->statusCode = $response->status();
        $result->message = 'Ok';
        $result->body = $response->object();

        if ($result->status) {
            $result->body = $result->body->data;
        } else {
            Log::error(json_encode($response->toPsrResponse()));
            $result->message = 'HTTP Request: ' . $response->toPsrResponse()->getReasonPhrase();
        }

        return $result;
    }

    private function getDefaultHeaders()
    {
        return [
            'X-API-KEY' => config('app.ai_api_key'),
            'Authorization' => 'Bearer ' . session('ai_token'),
        ];
    }
}
