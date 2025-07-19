# Markei SMTP configuration for Wordpress

Configure WordPress for sending mail via SMTP mail supporting encryption, (basic) auth and xoauth2. Configuration is done via wp-config.php.

For SMTP is supports plain connections but also SSL/TLS encryption (SMTPS) and STARTLS encryption.
Authentication is possible via normal authentication (basic) and XOAUTH2 (via service principal).

In the WordPress admin interface an e-mail test tool will become available to view the given configuration and let you sent a test mail.

This plugin is free, does not add signatures to your mail, no spam and no ad's.

## How to

Activate the plugin in WordPress admin.

Add the next line to `wordpress/wp-config.php` above `require_once(ABSPATH . 'wp-settings.php');`

    define('SMTP', 'smtp://localhost');

### Supported options

The configuration supports a URL format. Example: `protocol://host:port?optionA=valueA&optionB=valueB`.

#### Protocol SMTP

Simple configuration, no auth, port 25

    define('SMTP', 'smtp://localhost');

Simple configuration, no auth, with another port

    define('SMTP', 'smtp://my-smtp-server:587');

With basic authentication, insecure because no encryption is set so username and password are send readable

    define('SMTP', 'smtp://localhost:587?username=my-username&password=my-password');

Prefered way for authentification with STARTTLS encryption

    define('SMTP', 'smtp://localhost:587?encryption=tls&username=my-username&password=my-password');

Prefered way for authentification with SMTPS encryption. Note encryption=ssl even the server uses a TLS protocol instead of and old-SSL protocol.

    define('SMTP', 'smtp://localhost:465?encryption=ssl&username=my-username&password=my-password');

Using XOAUTH2 for authentication is possible when `client_credentials`-flow is supported, for example via service principals in 365. This is a good guide for [setting up a service principal for SMTP mailing in Microsoft 365/Office 365/Outlook Online/Exchange Online](https://www.maartendekeizer.nl/blog/detail/setup-smtp-xoauth2-with-microsoft-365). For 365 use server `smtp.office365.com`, port `587`, `token_endpoint=https://login.microsoftonline.com/<Directory (tenant ID)>/oauth2/v2.0/token` and `scope=https://outlook.office365.com/.default`.

Caching can be enabled via the option cache=1, this will speed up the processes for sending mails becauses not for each mail a new token is requested. The access token is cached using the WordPress transient system.

    define('SMTP', 'smtp://localhost:587?auth=xoauth2&client_id=my-client-id&client_secret=my-client-secret&token_endpoint=https://my-oauth-server/token&scope=sendmail&cache=1');

#### Protocol Sendmail

No options are available.

    define('SMTP', 'sendmail://');

#### Protocol mail

No options are available.

    define('SMTP', 'mail://');

## Install via composer

Install the plugin via Composer

    composer require markei/wordpress-smtp-configuration
