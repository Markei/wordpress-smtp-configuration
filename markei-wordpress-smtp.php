<?php
/*
Plugin Name:  Markei.nl WordPress SMTP configuration
Plugin URI:   https://github.com/markei/wordpress-smtp-configuration/
Description:  Configure WordPress for SMTP mail
Version:      1.0.0
Author:       Markei.nl
Author URI:   https://www.markei.nl
License:      MIT
License URI:  https://opensource.org/licenses/MIT
Text Domain:  markei-wordpress-smtp-configuration
Domain Path:  /languages
*/

function markei_wordpress_smtp__parse_settings() {
    if (defined('SMTP') === false) {
        return [];
    }
    $settings = [];
    preg_match('/(?<protocol>smtp|sendmail|mail*):\/\/(?<host>[\w\.]*)(:(?<port>\d*))?(\?(?<options>.*))?$/', SMTP, $settings);
    if (isset($settings['options'])) {
        $options = [];
        parse_str($settings['options'], $options);
        $settings['options'] = $options;
    }
    return $settings;
}

add_action( 'phpmailer_init', function ($phpmailer) {
    if (defined('SMTP') === false) {
        return;
    }
    $settings = markei_wordpress_smtp__parse_settings();
    if ($settings['protocol'] === 'smtp') {
        $phpmailer->isSMTP();
    } elseif ($settings['protocol'] === 'sendmail') {
        $phpmailer->isSendmail();
    }
    if (isset($settings['host'])) {
        $phpmailer->Host = $settings['host'];
    }
    if (isset($settings['port'])) {
        $phpmailer->Port = $settings['port'];
    }
    if (isset($settings['options']['username']) && isset($settings['options']['password'])) {
        $phpmailer->SMTPAuth = true;
        $phpmailer->Username = $settings['options']['username'];
        $phpmailer->Password = $settings['options']['password'];
    }
    if (isset($options['encryption'])) {
        $phpmailer->SMTPSecure = $settings['options']['encryption'];
    }
});

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

            $flash = '';

            if (isset($_POST['from']) && isset($_POST['to'])) {
                wp_mail($_POST['to'], 'Test email via WordPress', 'Hi, this is a test mail', 'From: ' . $_POST['from']);
                $flash = 'Send!';
            }

            include __DIR__ . DIRECTORY_SEPARATOR . 'test-form.html.php';
        }
    );
});