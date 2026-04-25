<?php

namespace Octany;

use Illuminate\Http\Request;
use Octany\Exceptions\OctanyInvalidSignatureException;

class Webhook
{
    const SIGNATURE_HEADER = 'Octany-Signature';

    public static function verify(Request $request, $secret)
    {
        $payload = $request->getContent();
        $signature = (string) $request->header(self::SIGNATURE_HEADER);

        if (! self::isValidSignature($payload, $signature, $secret)) {
            throw new OctanyInvalidSignatureException('Invalid Octany webhook signature.', 401);
        }

        $decoded = json_decode($payload, true);

        return is_array($decoded) ? $decoded : [];
    }

    public static function isValidSignature($payload, $signature, $secret)
    {
        if ($signature === '' || $secret === null || $secret === '') {
            return false;
        }

        $expected = hash_hmac('sha256', $payload, $secret);

        return hash_equals($expected, $signature);
    }
}
