<?php
/*
Plugin Name:  Markei SMTP configuration
Plugin URI:   https://github.com/markei/wordpress-smtp-configuration/
Description:  Configure WordPress for sending mail via SMTP mail supporting encryption, (basic) auth and xoauth2. Configuration is done via wp-config.php.
Version:      1.3.0
Author:       Markei.nl
Author URI:   https://www.markei.nl
License:      MIT
License URI:  https://opensource.org/licenses/MIT
Text Domain:  markei-smtp-configuration
*/

require_once __DIR__ . DIRECTORY_SEPARATOR . 'PHPMailer' . DIRECTORY_SEPARATOR . 'OAuthTokenProvider.php';

require_once __DIR__ . DIRECTORY_SEPARATOR . 'Dsn.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'Exception' . DIRECTORY_SEPARATOR . 'DsnException.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'Exception' . DIRECTORY_SEPARATOR . 'OAuthException.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'OAuthTokenProvider' . DIRECTORY_SEPARATOR . 'AbstractOAuthTokenProvider.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'OAuthTokenProvider' . DIRECTORY_SEPARATOR . 'ClientCredentialsOAuthTokenProvider.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'OAuthTokenProvider' . DIRECTORY_SEPARATOR . 'RefreshTokenOAuthTokenProvider.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'OAuthTokenProvider' . DIRECTORY_SEPARATOR . 'CodeOAuthTokenProvider.php';

use Markei\SmtpConfiguration\Dsn;
use Markei\SmtpConfiguration\OAuthTokenProvider\CodeOAuthTokenProvider;
use PHPMailer\PHPMailer\PHPMailer;

$plugin = new class {
    // transients
    const TRANSIENT_OAUTH2_STATE = 'markei_smtp_configuration___oauth2_state';
    const TRANSIENT_OAUTH2_ACCESS_TOKEN = 'markei_smtp_configuration___oauth2_access_token';

    // options
    const OPTION_OAUTH2_REFRESH_TOKEN = 'markei_smtp_configuration___oauth2_refresh_token';

    public function __construct()
    {
        add_action('phpmailer_init', function () {
            try {
                return $this->handlePhpMailerInit();
            } catch (\Exception $e) {
                include __DIR__ . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . 'admin-error.html.php';
                \wp_die();
            }
        });

        add_action('admin_menu', function () {
            try {
                return $this->handleAdminMenu();
            } catch (\Exception $e) {
                include __DIR__ . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . 'admin-error.html.php';
                \wp_die();
            }
        });
    }

    public function handlePhpMailerInit()
    {
        global $phpmailer;
        assert($phpmailer instanceof PHPMailer);

        // Load settings
        $settings = $this->loadSettings();

        // If no settings given, skip
        if ($settings === null) {
            return;
        }

        // Enable SMTP debugging
        if ($settings->debug === true) {
            $phpmailer->SMTPDebug = \PHPMailer\PHPMailer\SMTP::DEBUG_LOWLEVEL;
        }

        // Configurate PHPMailer based on protocol
        match ($settings->protocol) {
            Dsn::PROTOCOL_SMTP => $this->configurePhpMailForSmtp($phpmailer, $settings),
            Dsn::PROTOCOL_SENDMAIL => $this->configurePhpMailerForSendmail($phpmailer),
            Dsn::PROTOCOL_MAIL => $this->configurePhpMailerForMail($phpmailer)
        };
    }

    protected function configurePhpMailForSmtp(PHPMailer $phpmailer, Dsn $settings): void
    {
        $phpmailer->isSMTP();
        $phpmailer->Host = $settings->host;
        $phpmailer->Port = $settings->port;
        $phpmailer->SMTPSecure = match ($settings->encryption) {
            Dsn::ENCRYPTION_SSL => 'ssl',
            Dsn::ENCRYPTION_STARTTLS => 'tls',
            Dsn::ENCRYPTION_NONE => ''
        };
        match ($settings->auth) {
            Dsn::AUTH_METHOD_BASIC => $this->configurePhpMailForSmtpBasicAuth($phpmailer, $settings),
            Dsn::AUTH_METHOD_XOAUTH2 => $this->configurePhpMailForSmtpXOAuth2($phpmailer, $settings),
            Dsn::AUTH_METHOD_NONE => null
        };
    }

    protected function configurePhpMailForSmtpBasicAuth(PHPMailer $phpmailer, Dsn $settings): void
    {
        $phpmailer->SMTPAuth = true;
        $phpmailer->Username = $settings->username;
        $phpmailer->Password = $settings->password;
    }

    protected function configurePhpMailForSmtpXOAuth2(PHPMailer $phpmailer, Dsn $settings): void
    {
        if (empty($settings->authEndpoint)) {
            // Configure XOAUTH2 via a client credentials token provider (for example service principals)
            $provider = new Markei\SmtpConfiguration\OAuthTokenProvider\ClientCredentialsOAuthTokenProvider(
                $settings->clientId,
                $settings->clientSecret,
                $settings->tokenEndpoint,
                $settings->scope,
                $settings->useCache,
                self::TRANSIENT_OAUTH2_ACCESS_TOKEN
            );
        } else {
            // Configure XOAUTH2 via a refresh token provider
            $provider = new Markei\SmtpConfiguration\OAuthTokenProvider\RefreshTokenOAuthTokenProvider(
                $settings->clientId,
                $settings->clientSecret,
                $settings->tokenEndpoint,
                $settings->scope,
                $settings->useCache,
                self::TRANSIENT_OAUTH2_ACCESS_TOKEN,
                self::OPTION_OAUTH2_REFRESH_TOKEN
            );
        }
        $phpmailer->SMTPAuth = true;
        $phpmailer->AuthType = 'XOAUTH2';
        $phpmailer->setOAuth($provider);

        // because the xoauth2 auth needs to know which user is mailing, listen to phpmailer_init actions and set the from value as the user in the xoauth2 header
        // this only works fine when the wp_mail function is used
        $provider->setFrom($phpmailer->From);
    }

    protected function configurePhpMailerForSendmail(PHPMailer $phpmailer): void
    {
        $phpmailer->isSendmail();
    }

    protected function configurePhpMailerForMail(PHPMailer $phpmailer): void
    {
        $phpmailer->isMail();
    }

    /**
     * Add page in admin
     */
    public function handleAdminMenu()
    {
        add_submenu_page(
            'tools.php',
            'SMTP configuration',
            'SMTP configuration',
            'manage_options',
            'markei-smtp-configuration',
            function () {
                try {
                    if (!current_user_can('manage_options')) {
                        return;
                    }

                    $requestMethod = isset($_SERVER['REQUEST_METHOD']) && strtoupper($_SERVER['REQUEST_METHOD']) === 'POST' ? 'POST' : 'GET';
                    $settings = $this->loadSettings();
                    $blocks = [
                        'obtainToken' => $this->handleObtainToken($requestMethod, $_GET, $_POST, $settings),
                        'clearRefreshToken' => $this->handleClearRefreshToken($requestMethod, $_GET, $_POST, $settings),
                        'clearCache' => $this->handleClearCache($requestMethod, $_GET, $_POST, $settings),
                        'testMail' => $this->handleTestMail($requestMethod, $_GET, $_POST, $settings),
                        'settings' => $this->handleSettings($requestMethod, $_GET, $_POST, $settings),
                    ];

                    extract($blocks);
                    $adminUrl = admin_url('tools.php') . '?page=markei-smtp-configuration';
                    include __DIR__ . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . 'admin.html.php';
                } catch (\Exception $e) {
                    include __DIR__ . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . 'admin-error.html.php';
                    \wp_die();
                }
            }
        );
    }

    protected function handleSettings(string $requestMethod, array $query, array $request, ?Dsn $settings): array
    {
        return [
            'configuredViaConst' => defined('SMTP'),
            'settings' => $settings?->toArray()
        ];
    }

    protected function handleClearCache(string $requestMethod, array $query, array $request, ?Dsn $settings): array
    {
        if ($requestMethod !== 'POST' || isset($request['act']) === false || $request['act'] !== 'clear-cache') {
            return [
                'enabled' => $settings ? $settings->useCache : false,
                'accessTokenInCache' => (\get_transient(self::TRANSIENT_OAUTH2_ACCESS_TOKEN) !== false)
            ];
        }

        \check_admin_referer('markei-smtp-configuration___clear-cache');

        \delete_transient(self::TRANSIENT_OAUTH2_ACCESS_TOKEN);

        return [
            'enabled' => true,
            'accessTokenInCache' => false,
            'message' => 'Cache clear successfull'
        ];
    }

    protected function handleClearRefreshToken(string $requestMethod, array $query, array $request, ?Dsn $settings): array
    {
        if ($requestMethod !== 'POST' || isset($request['act']) === false || $request['act'] !== 'clear-refresh-token') {
            return [
                'enabled' => $settings ? (empty($settings->authEndpoint) === false) : false,
                'refreshTokenSet' => (\get_option(self::OPTION_OAUTH2_REFRESH_TOKEN, null) !== null)
            ];
        }

        \check_admin_referer('markei-smtp-configuration___clear-refresh-token');

        \delete_option(self::OPTION_OAUTH2_REFRESH_TOKEN);
        \delete_transient(self::TRANSIENT_OAUTH2_ACCESS_TOKEN); // remove related access token too

        return [
            'enabled' => true,
            'refreshTokenSet' => false,
            'message' => 'Refresh token clear successfull, obtain a new one before sending mail'
        ];
    }

    protected function handleTestMail(string $requestMethod, array $query, array $request, ?Dsn $settings): array
    {
        if ($requestMethod !== 'POST' || isset($request['act']) === false || $request['act'] !== 'test-mail') {
            return [
                'from' => \get_bloginfo('admin_email'),
                'to' => ''
            ];
        }

        \check_admin_referer('markei-smtp-configuration___test-mail');

        // load data from post request and validate addresses
        $from = isset($request['from']) ? is_email(wp_unslash($request['from'])) : '';
        $to = isset($request['to']) ? is_email(wp_unslash($request['to'])) : '';

        $start = microtime(true);
        $result = wp_mail($to, 'Test email via WordPress', 'Hi, this is a test mail', 'From: ' . $from);
        global $phpmailer;
        $duration = microtime(true) - $start;

        return [
            'from' => $from,
            'to' => $to,
            'duration' => $duration,
            'phpmailer' => $phpmailer,
            'result' => $result
        ];
    }

    protected function handleObtainToken(string $requestMethod, array $query, array $request, ?Dsn $settings): array
    {
        if (($requestMethod !== 'POST' || isset($request['act']) === false || $request['act'] !== 'obtain-token')
            && ($requestMethod !== 'GET' || isset($query['act']) === false || $query['act'] !== 'obtain-token')
            )
        {
            return [
                'enabled' => $settings ? (empty($settings->authEndpoint) === false) : false,
                'refreshTokenSet' => (\get_option(self::OPTION_OAUTH2_REFRESH_TOKEN, null) !== null)
            ];
        }

        if (isset($query['error'])) {
            return [
                'enabled' => true,
                'message' => 'Auth flow on token server not completed',
                'error' => $query['error']
            ];
        }

        // build url for redirect url
        $redirectUri = \plugin_dir_url(__FILE__) . 'oauth2-callback.php';

        if (isset($query['code'])) {
            if ($query['state'] !== \get_transient(self::TRANSIENT_OAUTH2_STATE)) {
                return [
                    'enabled' => true,
                    'message' => 'State mismatch, try again'
                ];
            }

            $oauthProvider = new CodeOAuthTokenProvider(
                $settings->clientId,
                $settings->clientSecret,
                $settings->tokenEndpoint,
                $settings->scope,
                $settings->useCache,
                self::TRANSIENT_OAUTH2_ACCESS_TOKEN,
                self::OPTION_OAUTH2_REFRESH_TOKEN,
                $query['code'],
                $redirectUri
            );
            $oauthProvider->getAccessToken();

            return [
                'enabled' => true,
                'message' => 'Completed'
            ];
        }

        \check_admin_referer('markei-smtp-configuration___obtain-token');

        // set up a state
        $state = md5(uniqid('', true));
        \set_transient(self::TRANSIENT_OAUTH2_STATE, $state, 60 * 60 * 15);

        $params = [
            'response_type' => 'code',
            'client_id' => $settings->clientId,
            'redirect_uri' => $redirectUri,
            'scope' => $settings->scope,
            'state' => $state
        ];
        foreach ($settings->authEndpointParameters as $name => $value) {
            if (isset($param[$name]) === true) {
                throw new \InvalidArgumentException('Could not set extra parameter for auth endpoint because it is already a normal parameter');
            }
            $params[$name] = $value;
        }

        // build url for auth endpoint
        $redirectTo = $settings->authEndpoint . '?' . http_build_query($params);

        return [
            'enabled' => true,
            'redirectTo' => $redirectTo
        ];
    }

    /**
     * Read settings from SMTP constant
     */
    public function loadSettings(): ?Dsn {
        // if constant is not defined, skip
        if (defined('SMTP') === false) {
            return null;
        }

        static $dsn = new Dsn(\SMTP);
        return $dsn;
    }
};

