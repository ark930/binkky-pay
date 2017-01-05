<?php

namespace App\Libraries;

use App\Exceptions\BadRequestException;
use GuzzleHttp\Client;
use Exception;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Log;

trait HttpClientTrait
{
    protected $httpClient = null;

    public function initHttpClient($baseUrl = '', array $headers = [])
    {
        $this->httpClient = new Client([
            'base_uri' => $baseUrl,
            'timeout' => 10.0,
            'headers' => $headers
        ]);
    }

    public function requestJson($method, $url, $data = null)
    {
        $options = ['json' => $data];

        return $this->request($method, $url, $options);
    }

    public function requestForm($method, $url, $data = null)
    {
        $options = ['form_params' => $data];

        return $this->request($method, $url, $options);
    }

    public function requestPlainText($method, $url, $data = null)
    {
        $options = ['body' => $data];

        return $this->request($method, $url, $options);
    }

    public function request($method, $url, $options = [])
    {
        try {
            Log::info($method . ' ' . $url);
            $res = $this->httpClient->request($method, $url, $options);
            $body =  $res->getBody();
            $content = $body->getContents();
            Log::info($content);
            return $content;
        } catch (Exception $e) {
            $this->exceptionHandler($e);
        }
    }

    protected function exceptionHandler(Exception $e)
    {
        if($e instanceof RequestException) {
            $response = $e->getResponse();
            if(is_null($response)) {
                throw new BadRequestException($e->getMessage());
            }

            $code = $response->getStatusCode();
            $body = $response->getBody();

            throw new BadRequestException($body, $code);
        }

        throw new BadRequestException($e->getMessage(), $e->getCode());
    }
}
