<?php

require_once __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR  . '..' . DIRECTORY_SEPARATOR . 'wp-load.php';

wp_redirect(admin_url('tools.php?' . http_build_query(array_filter([
    'page' => 'markei-smtp-configuration',
    'act' => 'obtain-token',
    'state' => isset($_GET['state']) ? $_GET['state'] : null,
    'code' => isset($_GET['code']) ? $_GET['code'] : null,
    'error' => isset($_GET['error']) ? $_GET['error'] : null,
]))));
exit;