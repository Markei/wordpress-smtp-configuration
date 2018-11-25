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

add_action( 'phpmailer_init', function ($phpmailer) {
    if (defined('SMTP') === false) {
        return;
    }
    $settings = [];
    preg_match('/(?<protocol>smtp|sendmail|mail*):\/\/(?<host>[\w\.]*)(:(?<port>.*))?\?(?<options>.*)/', SMTP, $settings);
    if (isset($settings['options'])) {
        parse_str($settings['options'], $options);
    }
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
    if (isset($options['username']) && isset($options['password'])) {
        $phpmailer->SMTPAuth = true;
        $phpmailer->Username = $options['username'];
        $phpmailer->Password = $options['password'];
    }
    if (isset($options['encryption'])) {
        $phpmailer->SMTPSecure = $options['encryption'];
    }
});