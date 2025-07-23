<?php

declare(strict_types=1);

namespace Markei\SmtpConfiguration\OAuthTokenProvider;

class RefreshTokenOAuthTokenProvider extends AbstractOAuthTokenProvider
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
    )
    {
        parent::__construct($clientId, $clientSecret, $tokenEndpoint, $scope, $cacheAccessToken, $transientKeyAccessToken);
    }

    protected function requestAccessToken(): array
    {
        $refreshToken = \get_option($this->optionKeyRefreshToken, null);
        if ($refreshToken === null) {
            throw new \RuntimeException('Could not start refresh token exchange, obtain a refresh token first via the WordPress admin');
        }
        $validUntil = \get_option($this->optionKeyRefreshTokenExpiry, null);
        if ($validUntil !== null) {
            if (time() > $validUntil) {
                throw new \RuntimeException('Refresh token is expired, obtain a new refresh token first via the WordPress admin');
            }
        }

        $data = $this->doTokenRequest([
                'grant_type' => 'refresh_token',
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret,
                'refresh_token' => $refreshToken,
            ], [
                'access_token',
                'expires_in',
                'refresh_token',
            ]
        );

        // save the new refresh token
        \update_option($this->optionKeyRefreshToken, $data['refresh_token']);
        \update_option($this->optionKeyRefreshTokenExpiry, isset($data['refresh_token_expires_in']) ? time() + intval($data['refresh_token_expires_in']) : null);

        // return access token and ttl
        return ['accessToken' => $data['access_token'], 'expiresIn' => $data['expires_in']];
    }
}