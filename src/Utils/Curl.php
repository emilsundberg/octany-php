<?php

namespace Octany\Utils;

use Psr\Http\Message\RequestInterface;

class Curl
{
    public static function fromRequest(RequestInterface $request): string
    {
        $command = 'curl -X '.$request->getMethod();

        foreach ($request->getHeaders() as $name => $values) {
            $command .= ' -H "'.$name.': '.implode(', ', $values).'"';
        }

        $body = (string) $request->getBody();

        if (! empty($body)) {
            $command .= " -d '".addslashes($body)."'";
        }

        $command .= ' "'.$request->getUri().'"';

        return $command;
    }
}
