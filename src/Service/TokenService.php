<?php

namespace App\Service;

class TokenService
{
    public function isTokenExpired($token): bool
    {
        $payload = $this->getTokenPayload($token);
        if (empty($payload) || !$payload['exp']) {
            return true;
        }

        return time() > $payload['exp'];
    }

    protected function getTokenPayload($token): mixed
    {
        $explode = explode('.', $token);
        if ($explode[1]) {
            return json_decode(base64_decode($explode[1]) ?: '', true);
        }

        return false;
    }

}
