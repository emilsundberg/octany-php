# Octany API PHP SDK

[![Latest Version on Packagist](https://img.shields.io/packagist/v/octany/octany-php.svg?style=flat-square)](https://packagist.org/packages/octany/octany-php)
[![GitHub Tests Action Status](https://img.shields.io/github/workflow/status/octany/octany-php/run-tests?label=tests)](https://github.com/octany/octany-php/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/workflow/status/octany/octany-php/Fix%20PHP%20code%20style%20issues?label=code%20style)](https://github.com/octany/octany-php/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)

This package is under development.

## Installation

```bash
composer require octany/octany-php
```

## Configuration

Publish the config file (optional — defaults read from env):

```bash
php artisan vendor:publish --tag=config --provider="Octany\Providers\OctanyServiceProvider"
```

Then set the following in `.env`:

```
OCTANY_ACCOUNT=your-account-uuid
OCTANY_API_KEY=your-api-key
OCTANY_API_URL=                       # optional, defaults to https://app.octany.com
OCTANY_WEBHOOK_SECRET=                # optional, used by Octany\Webhook::verify()
```

`config/octany-php.php` also exposes an `http_options` array which is passed
through to Laravel's HTTP client via `withOptions()` — useful for local dev
against self-signed certificates (e.g. `'verify' => false`).

## Usage

The service provider binds `Octany\OctanyClient` as a singleton, so you can
type-hint it or use the `Octany` facade:

```php
use Octany\Facades\Octany;

$subscription = Octany::subscriptions()->find(['reference_id' => $user->id]);
```

Or resolve the client directly:

```php
use Octany\OctanyClient;

$client = app(OctanyClient::class);
$subscriptions = $client->subscriptions()->all();
```

If you'd rather construct the client manually:

```php
$client = new OctanyClient(
    config('octany-php.account'),
    config('octany-php.api_key'),
    [
        'domain' => config('octany-php.api_url'),
        'http_options' => ['verify' => false], // dev-only
    ],
);
```

## Method reference

### `subscriptions()`

| Method                                                                              | Description                                                  |
|-------------------------------------------------------------------------------------|--------------------------------------------------------------|
| `all()`                                                                             | List all subscriptions for the account.                      |
| `find(array $filters)`                                                              | Find the first subscription matching the given filters.      |
| `order($subscriptionId, $amount, $description, $productId, array $options = [])`    | Create a one-off order against an existing subscription.     |
| `orders($subscriptionId, array $filters = [])`                                      | List orders for a subscription.                              |
| `createFromSubscriptionBillingMethod($subscriptionId, $productId, $options = [])`   | Start a new subscription using an existing billing method.   |

Filter keys supported by `find()` include `reference_id`, `customer_id`,
`status`, and `product_id`. `find()` returns the first match, or an empty
array when no match is found.

## Webhooks

The package ships a small helper for verifying the `Octany-Signature` HMAC
header on incoming webhooks:

```php
use Octany\Webhook;
use Octany\Exceptions\OctanyInvalidSignatureException;

Route::post('/octany/webhook', function (Request $request) {
    try {
        $payload = Webhook::verify($request, config('octany-php.webhook_secret'));
    } catch (OctanyInvalidSignatureException $e) {
        abort(401);
    }

    // dispatch jobs, sync local state, etc.
});
```

`Webhook::verify()` returns the decoded JSON payload as an array, or throws
`OctanyInvalidSignatureException` on a bad/missing signature. The constant-time
comparison and signature header name (`Octany-Signature`) are handled for you.

## Exception handling

API errors throw typed exceptions, all extending `Octany\Exceptions\OctanyException`:

| Status      | Exception                                              |
|-------------|--------------------------------------------------------|
| 401, 403    | `Octany\Exceptions\OctanyAuthenticationException`      |
| 404         | `Octany\Exceptions\OctanyNotFoundException`            |
| 422         | `Octany\Exceptions\OctanyValidationException`          |
| 429         | `Octany\Exceptions\OctanyRateLimitException`           |
| other       | `Octany\Exceptions\OctanyException`                    |

Each exception exposes the HTTP status (`$e->statusCode()`) and the raw
response body (`$e->responseBody()`).

```php
use Octany\Exceptions\OctanyNotFoundException;
use Octany\Exceptions\OctanyValidationException;

try {
    $client->subscriptions()->find(['reference_id' => $user->id]);
} catch (OctanyValidationException $e) {
    Log::warning('Octany rejected request', ['body' => $e->responseBody()]);
} catch (OctanyNotFoundException $e) {
    // ...
}
```

## Testing

Because the SDK uses Laravel's HTTP client under the hood, you can fake API
responses with `Http::fake()` in your application's test suite:

```php
use Illuminate\Support\Facades\Http;

Http::fake([
    '*/api/*/subscriptions*' => Http::response([
        'data' => [['id' => 76963688, 'status' => 'active']],
    ]),
]);
```

Run the package's own test suite with:

```bash
composer test
```

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
