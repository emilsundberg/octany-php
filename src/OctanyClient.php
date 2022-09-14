<?php

namespace Octany;

use Exception;
use Illuminate\Support\Arr;
use Zttp\Zttp;

class OctanyClient
{
    private $baseUrl = 'https://app.octany.com/api';

    private $accountId;

    private $key;

    private $latestResponse;

    public function __construct($accountId, $key, $options = [])
    {
        $this->accountId = $accountId;
        $this->key = $key;

        if ($domain = Arr::get($options, 'domain')) {
            $this->baseUrl = "$domain/api";
        }
    }

    public function subscriptions()
    {
        return new Subscription($this);
    }

    public function get($endpoint, $parameters = [])
    {
        $parameters['locale'] = Arr::get($parameters, 'locale', app()->getLocale());

        $this->latestResponse =
            Zttp::withHeaders([
                'X-API-Key' => $this->key,
            ])->get($this->url($endpoint), $parameters);

        return $this->response();
    }

    public function post($endpoint, $parameters = [])
    {
        $parameters['locale'] = Arr::get($parameters, 'locale', app()->getLocale());

        $this->latestResponse =
            Zttp::withHeaders([
                'X-API-Key' => $this->key,
            ])->post($this->url($endpoint), $parameters);

        return $this->response();
    }

    private function url($endpoint)
    {
        return "$this->baseUrl/$this->accountId/$endpoint";
    }

    protected function response()
    {
        if (! $this->latestResponse->isOk()) {
            throw new Exception(
                $this->latestResponse->getStatusCode().' – '.$this->latestResponse->getReasonPhrase()
            );
        }

        return $this->latestResponse->json();
    }
}
