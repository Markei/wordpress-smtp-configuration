<?php

namespace Markei\SmtpConfiguration;

class GeneralOAuthTokenProvider implements \PHPMailer\PHPMailer\OAuthTokenProvider
{
    /**
     * Client ID of the service principal
     * @var string
     */
    private $clientId;

    /**
     * Client secret of the service principal
     * @var string
     */
    private $clientSecret;

    /**
     * HTTP OAuth2/OIDC token endpoint
     * @var string
     */
    private $tokenEndpoint;

    /**
     * Scope list to request (multiple scopes splitted by a space)
     * @var string
     */
    private $scope;

    /**
     * Should we cache access tokens?
     * @var bool
     */
    private $cacheAccessToken;

    /**
     * Mailbox that is going to send the mail
     * @var string
     */
    private $mailbox;

    /**
     * When caching the same expiration time for the cache as for the access token is used.
     * This can lead into problems because we need some time from the moment to access token is generated and the cache is saved.
     * If this takes us 1 second the cache will return during 1 second an token that is no longer valid.
     * To prevent this we will shorten the access token expiration with a few seconds so the cache is invalidate before the access token should expire
     * @var int
     */
    private $numberOfSecondsToSubstractFromAccessTokenExpiry = 60;

    public function __construct(
        $clientId,
        $clientSecret,
        $tokenEndpoint,
        $scope,
        $cacheAccessToken
    ) {
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
        $this->tokenEndpoint = $tokenEndpoint;
        $this->scope = $scope;
        $this->cacheAccessToken = $cacheAccessToken;
    }

    protected function generateTransientKey($mailbox) {
        return 'Markei\\SmtpConfiguration\\GeneralOAuthTokenProvider___access_token___' . md5($mailbox);
    }

    public function getOauth64() {
        $accessToken = null;
        $ttl = 1;

        if ($this->cacheAccessToken === true) {
            $accessTokenFromCache = get_transient($this->generateTransientKey($this->mailbox));
            if ($accessTokenFromCache !== false) {
                $accessToken = $accessTokenFromCache;
            }
        }

        if ($accessToken === null) {
            $response = wp_remote_request($this->tokenEndpoint, [
                'method' => 'POST',
                'body' => [
                    'grant_type' => 'client_credentials',
                    'client_id' => $this->clientId,
                    'client_secret' => $this->clientSecret,
                    'scope' => $this->scope,
                ]
            ]);

            if ($response['response']['code'] !== 200) {
                wp_trigger_error(__METHOD__, 'Could not exchange client id/secret for access token', E_USER_ERROR);
            }

            $data = json_decode($response['body'], true);

            if (isset($data['access_token']) === false) {
                wp_trigger_error(__METHOD__, 'Invalid response receveid for token exchange', E_USER_ERROR);
            }

            $ttl = $data['expires_in'];
            $accessToken = $data['access_token'];
        }

        if ($this->cacheAccessToken === true) {
            set_transient($this->generateTransientKey($this->mailbox), $accessToken, $ttl - $this->numberOfSecondsToSubstractFromAccessTokenExpiry);
        }

        return base64_encode('user=' . $this->mailbox . "\1auth=Bearer " . $accessToken . "\1\1");
    }

    public function setFrom($mailbox) {
        $this->mailbox = $mailbox;
    }
}