<?php

namespace Octany;

use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Octany\Exceptions\OctanyAuthenticationException;
use Octany\Exceptions\OctanyException;
use Octany\Exceptions\OctanyNotFoundException;
use Octany\Exceptions\OctanyRateLimitException;
use Octany\Exceptions\OctanyValidationException;
use Octany\Utils\Curl;

class OctanyClient
{
    private $baseUrl = 'https://app.octany.com/api';

    private $accountId;

    private $key;

    private $latestResponse;

    private $httpOptions = [];

    public function __construct($accountId, $key, $options = [])
    {
        $this->accountId = $accountId;
        $this->key = $key;

        if ($domain = Arr::get($options, 'domain')) {
            $this->baseUrl = "$domain/api";
        }

        $this->httpOptions = Arr::get($options, 'http_options', []);
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

    protected function httpClient()
    {
        $options = array_merge($this->httpOptions, [
            'on_stats' => function ($stats) {
                if (config('app.debug') && config('octany-php.log.requests')) {
                    Log::channel(config('octany-php.log.channel'))
                        ->debug('[HTTP DEBUG] '.Curl::fromRequest($stats->getRequest()));
                }
            },
        ]);

        return Http::withHeaders([
            'X-API-Key' => $this->key,
        ])->retry(3, function ($attempt, $exception) {
            if ($exception instanceof RequestException) {
                $retryAfter = $exception->response->header('Retry-After');

                if ($retryAfter) {
                    return (int) $retryAfter * 1000;
                }
            }

            return $attempt * 1000;
        }, function ($exception) {
            return $exception instanceof RequestException
                && $exception->response->status() === 429;
        })->withOptions($options);
    }

    private function url($endpoint)
    {
        return "$this->baseUrl/$this->accountId/$endpoint";
    }

    protected function response()
    {
        if (! $this->latestResponse->successful()) {
            throw $this->makeException(
                $this->latestResponse->status(),
                $this->latestResponse->body()
            );
        }

        return $this->latestResponse->json();
    }

    protected function makeException($status, $body)
    {
        $message = "Octany API error ($status): $body";

        if ($status === 401 || $status === 403) {
            return new OctanyAuthenticationException($message, $status, $body);
        }

        if ($status === 404) {
            return new OctanyNotFoundException($message, $status, $body);
        }

        if ($status === 422) {
            return new OctanyValidationException($message, $status, $body);
        }

        if ($status === 429) {
            return new OctanyRateLimitException($message, $status, $body);
        }

        return new OctanyException($message, $status, $body);
    }
}
