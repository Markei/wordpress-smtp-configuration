    <?php if (isset($clearRefreshToken['enabled']) && $clearRefreshToken['enabled'] === true): ?>
        <h2>Clear refresh token</h2>
        <?php if (isset($clearRefreshToken['message'])): ?>
            <p><strong><?php echo esc_html($clearRefreshToken['message']); ?></strong></p>
        <?php endif; ?>
        <?php if ($clearRefreshToken['refreshTokenSet'] === false): ?>
            <p>No refresh token to clear, obtain a refresh token first.</p>
        <?php else: ?>
            <p>Without a refresh token mail sending mail is not possible. Are you sure? Maybe obtaining a new token is a better idea.</p>
            <form method="post" action="<?php echo esc_attr($adminUrl); ?>">
                <?php wp_nonce_field('markei-smtp-configuration___clear-refresh-token'); ?>
                <input type="hidden" name="act" value="clear-refresh-token">
                <p class="submit">
                    <input type="submit" class="button button-primary" value="Clear refresh token">
                </p>
            </form>
        <?php endif; ?>
    <?php endif; ?>