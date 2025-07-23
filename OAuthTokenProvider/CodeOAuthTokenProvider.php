<?php

declare(strict_types=1);

namespace Markei\SmtpConfiguration\OAuthTokenProvider;

if (defined('ABSPATH') === false) {
    exit;
}

class CodeOAuthTokenProvider extends AbstractOAuthTokenProvider
{
    public function __construct(
        string $clientId,
        string $clientSecret,
        string $tokenEndpoint,
        string $scope,
        bool $cacheAccessToken,
        string $transientKeyAccessToken,
        protected string $optionKeyRefreshToken,
        protected string $optionKeyRefreshTokenExpiry,
        protected string $code,
        protected string $redirectUri
    )
    {
        parent::__construct($clientId, $clientSecret, $tokenEndpoint, $scope, $cacheAccessToken, $transientKeyAccessToken);
    }

    protected function requestAccessToken(): array
    {
        $data = $this->doTokenRequest(array_filter([
                'grant_type' => 'authorization_code',
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret,
                'code' => $this->code,
                'redirectUri' => $this->redirectUri
            ]), [
                'access_token',
                'expires_in',
                'refresh_token',
            ]
        );

        // save the refresh token
        \update_option($this->optionKeyRefreshToken, $data['refresh_token']);
        \update_option($this->optionKeyRefreshTokenExpiry, isset($data['refresh_token_expires_in']) ? time() + intval($data['refresh_token_expires_in']) : null);

        // return access token and ttl
        return ['accessToken' => $data['access_token'], 'expiresIn' => $data['expires_in']];
    }
}