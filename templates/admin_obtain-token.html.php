<?php
    if (defined('ABSPATH') === false) {
        exit;
    }
?>
    <?php if ($obtainToken['enabled'] === true): ?>
        <h2>Obtain access and refresh token</h2>
        <?php if (isset($obtainToken['refreshTokenSet']) === true && $obtainToken['refreshTokenSet'] === true): ?>
            <p>We already have a refresh token.</p>
            <?php if ($obtainToken['refreshTokenExpiry'] !== null): ?>
                <?php if ($obtainToken['refreshTokenExpiry'] > time()): ?>
                    <p>The current refresh token is valid until <?php echo esc_html(gmdate('c', $obtainToken['refreshTokenExpiry'])); ?></p>
                    <p>The refresh token will be automaticly renewed when a mail is send and the cached access token is expired. Obtaining a new one is only necessary if the current one is revoked by the mail provider.</p>
                <?php else: ?>
                    <p>The current refresh token is expired. Obtain a new token.</p>
                <?php endif; ?>
            <?php else: ?>
                <p>Obtaining a new one is only necessary if the current one is revoked by the mail provider.</p>
            <?php endif; ?>
        <?php endif; ?>
        <?php if (isset($obtainToken['redirectTo']) === true): ?>
            <p><a href="<?php echo esc_attr($obtainToken['redirectTo']); ?>">Redirecting to <?php echo esc_html($obtainToken['redirectTo']); ?></a></p>
            <script type="text/javascript">
                window.location = <?php echo json_encode($obtainToken['redirectTo']); ?>;
            </script>
        <?php endif; ?>
        <?php if (empty($obtainToken['message']) === false): ?>
            <p><strong><?php echo esc_html($obtainToken['message']); ?></strong></p>
        <?php endif; ?>
        <form method="post" action="<?php echo esc_attr($adminUrl); ?>">
            <?php wp_nonce_field('markei-smtp-configuration___obtain-token'); ?>
            <input type="hidden" name="act" value="obtain-token">
            <p class="submit">
                <input type="submit" class="button button-primary" value="Obtain token">
            </p>
        </form>
    <?php endif; ?>