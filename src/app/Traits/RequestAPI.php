<?php

namespace App\Traits;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

trait RequestAPI
{
    public function sendGETRequest($url, $params = [], $headers = [])
    {
        $response = Http::withHeaders($headers)->get($url, $params);

        return $this->getResponse($response, $url);
    }

    public function sendPOSTRequest($url, $body, $headers = [])
    {
        $response = Http::withHeaders($headers)->post($url, $body);

        return $this->getResponse($response, $url);
    }

    public function sendPUTRequest($url, $body, $headers = [])
    {
        $response = Http::withHeaders($headers)->put($url, $body);

        return $this->getResponse($response, $url);
    }

    public function sendDELETERequest($url, $body = [], $headers = [])
    {
        $response = Http::withHeaders($headers)->delete($url, $body);

        return $this->getResponse($response, $url);
    }

    /**
     * @param \Illuminate\Http\Client\Response $response
     * @param string $url
     * @return \stdClass
     */
    private function getResponse($response, $url = '')
    {
        $result = new \stdClass();
        $result->status = $response->successful();
        $result->statusCode = $response->status();
        $result->message = 'Ok';
        $result->body = $response->object();

        if ($result->status) {
            $result->body = $result->body->data;
        } else {
            Log::error($url . "       ----------       " . json_encode($result));

            if (object_get($result, 'body.message')) {
                $result->message = $result->body->message;
            } else {
                $result->message = 'HTTP Request: ' . $response->toPsrResponse()->getReasonPhrase();
            }
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
