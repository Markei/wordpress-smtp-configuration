<?php
    if (defined('ABSPATH') === false) {
        exit;
    }
?>
    <?php if ($clearCache['enabled'] === true): ?>
        <h2>Clear cache (access token only)</h2>
        <?php if (isset($clearCache['message'])): ?>
            <p><strong><?php echo esc_html($clearCache['message']); ?></strong></p>
        <?php endif; ?>
        <?php if ($clearCache['accessTokenInCache'] === false): ?>
            <p>Cache is empty.</p>
        <?php else: ?>
            <p>Access token is in cache. Clear the cache the remove the access token. We will try to get a new access token on the next time sending mail.</p>
            <form method="post" action="<?php echo esc_attr($adminUrl); ?>">
                <?php wp_nonce_field('markei-smtp-configuration___clear-cache'); ?>
                <input type="hidden" name="act" value="clear-cache">
                <p class="submit">
                    <input type="submit" class="button button-primary" value="Clear cache">
                </p>
            </form>
        <?php endif; ?>
    <?php endif; ?>