<?php
/*
Plugin Name:  SMTP configuration
Plugin URI:   https://github.com/markei/wordpress-smtp-configuration/
Description:  Configure WordPress for SMTP mail with support for auth en XOAUTH
Version:      1.2.0
Author:       Markei.nl
Author URI:   https://www.markei.nl
License:      MIT
License URI:  https://opensource.org/licenses/MIT
Text Domain:  smtp-configuration
*/

require_once __DIR__ . DIRECTORY_SEPARATOR . 'PHPMailer' . DIRECTORY_SEPARATOR . 'OAuthTokenProvider.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'GeneralOAuthTokenProvider.php';

use PHPMailer\PHPMailer\PHPMailer;

/**
 * Read settings from SMTP constant
 * @return array
 */
function smtp_configuration__parse_settings() {
    // if constant is not defined, skip
    if (defined('SMTP') === false) {
        return [];
    }

    $settings = [];
    preg_match('/(?<protocol>smtp|sendmail|mail*):\/\/(?<host>[\w\.]*)(:(?<port>\d*))?(\?(?<options>.*))?$/', SMTP, $settings);
    if (isset($settings['options']) === false) {
        $settings['options'] = '';
    }

    $options = [];
    parse_str($settings['options'], $options);
    $settings['options'] = $options;

    // set some defaults
    if ($settings['protocol'] === 'smtp' && isset($settings['options']['auth']) === false && isset($settings['options']['username']) === false) {
        $settings['options']['auth'] = 'none';
    }
    if ($settings['protocol'] === 'smtp' && isset($settings['options']['username']) === true && isset($settings['options']['auth']) === false) {
        $settings['options']['auth'] = 'basic';
    }
    if ($settings['protocol'] === 'smtp' && $settings['options']['auth'] === 'xoauth2' && isset($settings['options']['cache']) === false) {
        $settings['options']['cache'] = false;
    }

    // convert some settings
    $settings['options']['cache'] = isset($settings['options']['cache']) ? boolval($settings['options']['cache']) : false;

    return $settings;
}

/**
 * Hook into the phpmailer setup
 */
add_action('phpmailer_init', function ($phpmailer) {
    // If constant is not defined, skip this action
    if (defined('SMTP') === false) {
        return;
    }

    assert($phpmailer instanceof PHPMailer);

    // Enable SMTP debugging
    //$phpmailer->SMTPDebug = \PHPMailer\PHPMailer\SMTP::DEBUG_LOWLEVEL;

    // Load settings
    $settings = smtp_configuration__parse_settings();

    // Configurate PHPMailer based on protocol
    if ($settings['protocol'] === 'smtp') {
        // Configure for SMTP
        $phpmailer->isSMTP();
        if (isset($settings['host'])) {
            $phpmailer->Host = $settings['host'];
        }
        if (isset($settings['port'])) {
            $phpmailer->Port = $settings['port'];
        }
        if (isset($settings['options']['encryption'])) {
            $phpmailer->SMTPSecure = $settings['options']['encryption'];
        }

        // By default no auth is applied ($settings['options']['auth'] === 'none')
        if ($settings['options']['auth'] === 'basic') {
            // Traditional smtp auth (basic auth)
            $phpmailer->SMTPAuth = true;
            $phpmailer->Username = $settings['options']['username'];
            $phpmailer->Password = $settings['options']['password'];
        } elseif ($settings['options']['auth'] === 'xoauth2') {
            // Configure XOAUTH2 via a general token provider (based on service principals)
            $provider = new Markei\SmtpConfiguration\GeneralOAuthTokenProvider(
                $settings['options']['client_id'],
                $settings['options']['client_secret'],
                $settings['options']['token_endpoint'],
                $settings['options']['scope'],
                $settings['options']['cache']
            );
            $phpmailer->SMTPAuth = true;
            $phpmailer->AuthType = 'XOAUTH2';
            $phpmailer->setOAuth($provider);

            // because the xoauth2 auth needs to know which user is mailing, listen to phpmailer_init actions and set the from value as the user in the xoauth2 header
            // this only works fine when the wp_mail function is used
            //add_action('phpmailer_init', function ($phpmailer) use ($provider) {
            //    assert($phpmailer instanceof PHPMailer);
            $provider->setFrom($phpmailer->From);
            //}, 1000000);
        }
    } elseif ($settings['protocol'] === 'sendmail') {
        // Configure for Sendmail
        $phpmailer->isSendmail();
    } else {
        // By default use PHP internal mail function
        $phpmailer->isMail();
    }

});

/**
 * Add page in admin
 */
add_action('admin_menu', function () {
    add_submenu_page(
        'tools.php',
        'SMTP test',
        'SMTP test',
        'manage_options',
        'wordpress-smtp-configuration',
        function () {
            if (!current_user_can('manage_options')) {
                return;
            }

            $from = get_bloginfo('admin_email');
            $to = '';

            if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
                // check nonce
                check_admin_referer('smtp-configuration-test-mail');

                // load data from post request and validate addresses
                $from = isset($_POST['from']) ? is_email(wp_unslash($_POST['from'])) : '';
                $to = isset($_POST['to']) ? is_email(wp_unslash($_POST['to'])) : '';

                // send mail when from and to are given
                if (empty($from) === false && empty($to) === false) {
                    $start = microtime(true);
                    $result = wp_mail($to, 'Test email via WordPress', 'Hi, this is a test mail', 'From: ' . $from);
                    global $phpmailer;
                    $duration = microtime(true) - $start;
                }
            }

            include __DIR__ . DIRECTORY_SEPARATOR . 'test-form.html.php';
        }
    );
});
