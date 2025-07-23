<?php

declare(strict_types=1);

namespace Markei\SmtpConfiguration\OAuthTokenProvider;

use Markei\SmtpConfiguration\Exception\OAuthException;

if (defined('ABSPATH') === false) {
    exit;
}

abstract class AbstractOAuthTokenProvider implements \PHPMailer\PHPMailer\OAuthTokenProvider
{
    /**
     * Mailbox that is going to send the mail
     * @var string
     */
    protected $mailbox;

    /**
     * When caching the same expiration time for the cache as for the access token is used.
     * This can lead into problems because we need some time from the moment to access token is generated and the cache is saved.
     * If this takes us 1 second the cache will return during 1 second an token that is no longer valid.
     * To prevent this we will shorten the access token expiration with a few seconds so the cache is invalidate before the access token should expire
     * @var int
     */
    protected $numberOfSecondsToSubstractFromAccessTokenExpiry = 60;

    public function __construct(
        protected string $clientId,
        protected string $clientSecret,
        protected string $tokenEndpoint,
        protected string $scope,
        protected bool $cacheAccessToken,
        protected string $transientKeyAccessToken,
    )
    {
        //
    }

    abstract protected function requestAccessToken(): array;

    public function getAccessToken(): string
    {
        // if caching is enabled, try to load from cache
        if ($this->cacheAccessToken === true) {
            $accessTokenFromCache = \get_transient($this->transientKeyAccessToken);
            if ($accessTokenFromCache !== false) {
                return $accessTokenFromCache;
            }
        }

        // no caching or no access token available
        ['accessToken' => $accessToken, 'expiresIn' => $expiresIn] = $this->requestAccessToken();

        // if caching is enabled, save in cache
        if ($this->cacheAccessToken === true) {
            \set_transient($this->transientKeyAccessToken, $accessToken, $expiresIn - $this->numberOfSecondsToSubstractFromAccessTokenExpiry);
        }

        return $accessToken;
    }

    protected function doTokenRequest(array $body, array $expectedFields): array
    {
        $response = \wp_remote_request($this->tokenEndpoint, [
            'method' => 'POST',
            'body' => $body
        ]);

        if (is_array($response) === false && $response instanceof \WP_Error) {
            assert($response instanceof \WP_Error);
            throw new OAuthException('Call to token endpoint failed', intval($response->get_error_code), $response->get_error_message(), ['errorData' => $response->get_all_error_data()]);
        }
        if ($response['response']['code'] !== 200) {
            throw new OAuthException('Token endpoint does not respond with 200 status', $response['response']['code'], $response['response']['message'], ['responseBody' => $response['body'], 'responseHeaders' => $response['headers']->getAll()]);
        }

        $data = json_decode($response['body'], true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new OAuthException('Response from token endpoint could not converted to string', $response['response']['code'], json_last_error_msg(), ['responseBody' => $response['body'], 'responseHeaders' => $response['headers']->getAll()]);
        }

        foreach ($expectedFields as $expectedField) {
            if (isset($data[$expectedField]) === false) {
                throw new OAuthException('Invalid response received for token exchange, missing expected field', $response['response']['code'], '', ['missingField' => $expectedField, 'givenFields' => array_keys($data)]);
            }
        }

        return $data;
    }

    /**
     * @return string
     */
    public function getOauth64()
    {
        return base64_encode('user=' . $this->mailbox . "\1auth=Bearer " . $this->getAccessToken() . "\1\1");
    }

    public function setFrom(string $mailbox): void
    {
        $this->mailbox = $mailbox;
    }
}