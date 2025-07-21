<?php

declare(strict_types=1);

namespace Markei\SmtpConfiguration\OAuthTokenProvider;

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

        // return access token and ttl
        return ['accessToken' => $data['access_token'], 'expiresIn' => $data['expires_in']];
    }
}