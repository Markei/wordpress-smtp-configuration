Markei WordPress SMTP Configuration
==============================

Install the plugin via Composer

    composer require markei/wordpress-smtp-configuration

Activate the plugin in WordPress admin

Add the next two lines to `wordpress/wp-config.php` above `require_once(ABSPATH . 'wp-settings.php');`

    define('SMTP', 'smtp://localhost:587?encryption=tls&username=&password=')
    
