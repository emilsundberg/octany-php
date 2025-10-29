<?php

namespace Octany;

class Subscription
{
    private $client;

    public function __construct(OctanyClient $client)
    {
        $this->client = $client;
    }

    public function all()
    {
        return $this->client->get('subscriptions');
    }

    public function find($filters)
    {
        $response = $this->client->get('subscriptions', ['filter' => $filters]);

        return $response['data'][0] ?? [];
    }

    public function order($subscriptionId, $amount, $description)
    {
        $this->client->post("subscription/$subscriptionId/order", [
            'amount' => $amount,
            'description' => $description,
        ]);
    }

    public function createFromSubscriptionBillingMethod($subscriptionId, $productId, $options = [])
    {
        $data = collect([
            'type' => 'from_subscription',
            'subscription_id' => $subscriptionId,
            'product_id' => $productId,
            'custom_amount' => $options['custom_amount'] ?? null,
            'reference_id' => $options['reference_id'] ?? null,
            'reference_name' => $options['reference_name'] ?? null,
        ])->reject(fn ($value) => blank($value))->all();

        return $this->client->post('subscriptions', $data);
    }
}
