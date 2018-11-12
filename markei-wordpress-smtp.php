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
    if (is_defined('SMTP') === false) {
        return;
    }
    $settings = [];
    preg_match('/(?<protocol>smtp|sendmail|mail*):\/\/(?<host>[\w\.]*)(:(?<port>.*))?\?(?<options>.*)/', SMTP, $settings);
    if (isset($settings['options'])) {
        $options = parse_str($settings['options'], $settings);
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
    if (isset($settings['username'])) {
        $phpmailer->Username = $settings['username'];
        $phpmailer->SMTPAuth = true;
    }
    if (isset($settings['password'])) {
        $phpmailer->Password = $settings['password'];
        $phpmailer->SMTPAuth = true;
    }
    if (isset($settings['encryption'])) {
        $phpmailer->SMTPSecure = $settings['encryption'];
    }
});