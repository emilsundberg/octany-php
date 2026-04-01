<?php

namespace Octany;

use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Octany\Utils\Curl;

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

        $client = $this->httpClient();

        $this->latestResponse = $client->get($this->url($endpoint), $parameters);

        return $this->response();
    }

    public function post($endpoint, $parameters = [])
    {
        $parameters['locale'] = Arr::get($parameters, 'locale', app()->getLocale());

        $this->latestResponse = $this->httpClient()
            ->post($this->url($endpoint), $parameters);

        return $this->response();
    }

    private function httpClient()
    {
        return Http::withHeaders([
            'X-API-Key' => $this->key,
        ])->retry(3, function ($attempt, $exception) {
            if ($exception instanceof \Illuminate\Http\Client\RequestException) {
                $retryAfter = $exception->response->header('Retry-After');

                if ($retryAfter) {
                    return (int) $retryAfter * 1000;
                }
            }

            return $attempt * 1000;
        }, function ($exception) {
            return $exception instanceof \Illuminate\Http\Client\RequestException
                && $exception->response->status() === 429;
        })->withOptions([
            'on_stats' => function ($stats) {
                if (config('app.debug') && config('octany-php.log.requests')) {
                    Log::channel(config('octany-php.log.channel'))
                        ->debug('[HTTP DEBUG] '.Curl::fromRequest($stats->getRequest()));
                }
            },
        ]);
    }

    private function url($endpoint)
    {
        return "$this->baseUrl/$this->accountId/$endpoint";
    }

    protected function response()
    {
        if (! $this->latestResponse->successful()) {
            throw new Exception(
                $this->latestResponse->status().' – '.$this->latestResponse->serverError()
            );
        }

        return $this->latestResponse->json();
    }
}
