<?php

// This interface is not shipped with the PHPMailer included in WordPress
// Define it when it not exists
// Source https://github.com/PHPMailer/PHPMailer/blob/dfa731a087042ef3a70793369147a8293320012f/src/OAuthTokenProvider.php
// License https://github.com/PHPMailer/PHPMailer/blob/dfa731a087042ef3a70793369147a8293320012f/LICENSE

namespace PHPMailer\PHPMailer;

if (defined('ABSPATH') === false) {
    exit;
}

if (interface_exists('PHPMailer\\PHPMailer\\OAuthTokenProvider') === false) {
    interface OAuthTokenProvider {
        /**
         * Generate a base64-encoded OAuth token ensuring that the access token has not expired.
         * The string to be base 64 encoded should be in the form:
         * "user=<user_email_address>\001auth=Bearer <access_token>\001\001"
         *
         * @return string
         */
        public function getOauth64();
    }
}
