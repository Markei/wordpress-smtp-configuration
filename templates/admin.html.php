<?php
    if (defined('ABSPATH') === false) {
        exit;
    }
?>
<div class="wrap">

    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>

    <?php include __DIR__ . DIRECTORY_SEPARATOR . 'admin_configuration.html.php'; ?>
    <?php include __DIR__ . DIRECTORY_SEPARATOR . 'admin_obtain-token.html.php'; ?>
    <?php include __DIR__ . DIRECTORY_SEPARATOR . 'admin_clear-refresh-token.html.php'; ?>
    <?php include __DIR__ . DIRECTORY_SEPARATOR . 'admin_clear-cache.html.php'; ?>
    <?php include __DIR__ . DIRECTORY_SEPARATOR . 'admin_test-mail.html.php'; ?>

</div>