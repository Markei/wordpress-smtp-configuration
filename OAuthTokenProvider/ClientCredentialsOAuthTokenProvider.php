<?php

declare(strict_types=1);

namespace Markei\SmtpConfiguration\OAuthTokenProvider;

if (defined('ABSPATH') === false) {
    exit;
}

class ClientCredentialsOAuthTokenProvider extends AbstractOAuthTokenProvider
{
    protected function requestAccessToken(): array
    {
        $data = $this->doTokenRequest([
                'grant_type' => 'client_credentials',
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret,
                'scope' => $this->scope,
            ], [
                'access_token',
                'expires_in'
            ]
        );

        // return access token and ttl
        return ['accessToken' => $data['access_token'], 'expiresIn' => $data['expires_in']];
    }
}