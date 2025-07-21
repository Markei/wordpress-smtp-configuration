<?php
    use Markei\SmtpConfiguration\Exception\DsnException;
use Markei\SmtpConfiguration\Exception\OAuthException;

?><div class="wrap">

    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>

    <?php if ($e instanceof DsnException): ?>
        <p>Whoops, invalid configuration for SMTP configuration plugin found.</p>

        <?php if (defined('WP_DEBUG') === false || WP_DEBUG !== true): ?>
            <p>Set WP_DEBUG=true for more information.</p>
        <?php else: ?>
            <p>Message: <strong><?php echo esc_html($e->getMessage()); ?></strong></p>
            <?php foreach ($e->data as $k => $v): ?>
                <p>
                    <?php echo esc_html($k); ?>:
                    <strong><?php echo esc_html(is_scalar($v) ? $v : print_r($v, true)); ?></strong>
                </p>
            <?php endforeach; ?>
        <?php endif; ?>
    <?php elseif ($e instanceof OAuthException): ?>
        <p>Whoops, exception in OAuth flow.</p>

        <?php if (defined('WP_DEBUG') === false || WP_DEBUG !== true): ?>
            <p>Set WP_DEBUG=true for more information.</p>
        <?php else: ?>
            <p>Message: <strong><?php echo esc_html($e->getMessage()); ?></strong></p>
            <p>Http status code: <strong><?php echo esc_html($e->httpStatus); ?></strong></p>
            <p>Remote error: <strong><?php echo esc_html($e->remoteError); ?></strong></p>

            <?php foreach ($e->data as $k => $v): ?>
                <p>
                    <?php echo esc_html($k); ?>:
                    <strong><?php echo esc_html(is_scalar($v) ? $v : print_r($v, true)); ?></strong>
                </p>
            <?php endforeach; ?>
        <?php endif; ?>
    <?php else: ?>
        <p>Whoops, an error occurred.</p>

        <?php if (defined('WP_DEBUG') === false || WP_DEBUG !== true): ?>
            <p>Set WP_DEBUG=true for more information.</p>
        <?php else: ?>
            <p>Message: <strong><?php echo esc_html($e->getMessage()); ?></strong></p>
            <p>File: <strong><?php echo esc_html($e->getFile()); ?></strong></p>
            <p>Line: <strong><?php echo esc_html($e->getLine()); ?></strong></p>
        <?php endif; ?>
    <?php endif; ?>

</div>