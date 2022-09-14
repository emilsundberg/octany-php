<?php

namespace Octany;

class Subscription
{
    private OctanyClient $client;

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

        if ($response['pagination']['count'] === 1) {
            return $response['data'][0];
        }

        return null;
    }

    public function order($subscriptionId, $amount, $description)
    {
        $this->client->post("subscription/$subscriptionId/order", [
            'amount' => $amount,
            'description' => $description,
        ]);
    }
}
