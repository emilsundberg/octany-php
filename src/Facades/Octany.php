<?php

namespace Octany\Facades;

use Illuminate\Support\Facades\Facade;
use Octany\OctanyClient;

/**
 * @method static \Octany\Subscription subscriptions()
 * @method static array get(string $endpoint, array $parameters = [])
 * @method static array post(string $endpoint, array $parameters = [])
 *
 * @see \Octany\OctanyClient
 */
class Octany extends Facade
{
    protected static function getFacadeAccessor()
    {
        return OctanyClient::class;
    }
}
