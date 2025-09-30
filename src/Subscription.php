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

        return $response['data'][0];
    }

    public function order($subscriptionId, $amount, $description)
    {
        $this->client->post("subscription/$subscriptionId/order", [
            'amount' => $amount,
            'description' => $description,
        ]);
    }
}
