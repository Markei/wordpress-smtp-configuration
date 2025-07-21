=== Markei SMTP configuration ===
Contributors: markeinl
Tags: mail, smtp, xoauth2, oauth
Requires at least: 6.0.0
Tested up to: 6.8
Requires PHP: 8.2
Stable Tag: 1.3.0
License: MIT
License URI: https://opensource.org/license/mit

Configure WordPress for sending mail via SMTP mail supporting encryption, (basic) auth and xoauth2. Configuration is done via wp-config.php.

== Description ==
For SMTP is supports plain connections but also SSL/TLS encryption (SMTPS) and STARTLS encryption.
Authentication is possible via normal authentication (basic) and XOAUTH2. For XOAUTH2 authentification as a user (Authorization Code Flow) or service principal (Client Credential Flow) is possible. This makes it possible to mail via Gmail or Microsoft 365.

In the WordPress admin interface an e-mail test tool will become available to view the given configuration and let you sent a test mail.

This plugin is free, does not add signatures to your mail, no spam and no ad's.

== Installation ==
Activate the plugin in WordPress admin.

Add the next line to `wordpress/wp-config.php` above `require_once(ABSPATH . \'wp-settings.php\');`

    define(\'SMTP\', \'smtp://localhost\');



The configuration supports a URL format. Example: `protocol://host:port?optionA=valueA&optionB=valueB`.



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

XOAUTH2 with client credentials (e.g. Microsoft 365)

Define `client_id`, `client_secret`, `token_endpoint` and `scope` as parameters. A access token is requested from the token_endpoint each time a mail is sent.

> This is a good guide for [setting up a service principal for SMTP mailing in Microsoft 365/Office 365/Outlook Online/Exchange Online](https://www.maartendekeizer.nl/blog/detail/setup-smtp-xoauth2-with-microsoft-365).
For 365 use server `smtp.office365.com`, port `587`, `token_endpoint=https://login.microsoftonline.com/<Directory (tenant ID)>/oauth2/v2.0/token` and `scope=https://outlook.office365.com/.default`.

Caching can be enabled via the option cache=1, this will speed up the processes for sending mails becauses not for each mail a new token is requested. The access token is cached using the WordPress transient system.

    define('SMTP', 'smtp://localhost:587?auth=xoauth2&client_id=my-client-id&client_secret=my-client-secret&token_endpoint=https://my-oauth-server/token&scope=sendmail&cache=1');

Example for Microsoft 365

    define('SMTP', 'smtp://smtp.office365.com:587?auth=xoauth2&client_id=<Client ID>&client_secret=<Client Secret>&token_endpoint=https://login.microsoftonline.com/<Directory (tenant ID)>/oauth2/v2.0/token&scope=https://outlook.office365.com/.default&cache=1');

XOAUTH2 with authorization code flow (e.g. Gmail)

The plugin will use the following redirect_uri which much be configured in the Authorization server:

    https://my-wordpress-website.tld/wp-content/plugins/smtp-configuration/oauth2-callback.php

Define `client_id`, `client_secret`, `token_endpoint`, `auth_endpoint` and `scope` as parameters. Extra parameters for the authorization server can be set via `auth_endpoint_parameters` which accepts an key-value array.

> For Gmail use server `smtp.gmail.com`, port `587`, `token_endpoint=https://oauth2.googleapis.com/token`, `auth_endpoint=https://accounts.google.com/o/oauth2/auth`, `auth_endpoint_parameters[prompt]=consent&auth_endpoint_parameters[access_type]=offline` and `scope=https://mail.google.com/`.

Caching can be enabled via the option cache=1, this will speed up the processes for sending mails becauses not for each mail the refresh token is rotated. The access token is cached using the WordPress transient system.

    define('SMTP', 'smtp://smtp.gmail.com:587?auth=xoauth2&client_id=my-client-id&client_secret=my-client-secret&token_endpoint=https://my-oauth-server/token&scope=sendmail%20offline&auth_endpoint=https://my-oauth-server/auth&cache=1');

Example for Gmail (replace your client id and secret):

    define('SMTP', 'smtp://smtp.gmail.com:587?auth=xoauth2&client_id=<Client ID>&client_secret=<Client Secret>&token_endpoint=https://oauth2.googleapis.com/token&scope=https://mail.google.com/&auth_endpoint=https://accounts.google.com/o/oauth2/auth&cache=1&auth_endpoint_parameters[prompt]=consent&auth_endpoint_parameters[access_type]=offline');

After configuration and enabling the plugin go to the SMTP configuration page in the Tools menu in the WordPress admin. Click on the *Obtain token* button.