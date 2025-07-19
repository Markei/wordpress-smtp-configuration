<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    <h2>Configuration</h2>
    <table class="wp-list-table widefat fixed striped table-view-list">
        <?php foreach (smtp_configuration__parse_settings() as $setting => $value): ?>
            <?php if (is_numeric($setting) === false): ?>
                <tr>
                    <th><strong><?php echo esc_html($setting); ?></strong></th>
                    <td>
                        <?php if (is_array($value)): ?>
                            <table class="wp-list-table widefat fixed striped table-view-list">
                                <?php foreach ($value as $option => $optionValue): ?>
                                    <tr>
                                        <th><?php echo esc_html($option); ?></th>
                                        <td>
                                            <?php if ($option !== 'password' && $option !== 'client_secret'): ?>
                                                <?php if (is_bool($optionValue)): ?>
                                                    <?php echo $optionValue ? 'true' : 'false'; ?>
                                                <?php else: ?>
                                                    <?php echo esc_html($optionValue); ?>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                ***
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </table>
                        <?php else: ?>
                            <?php echo esc_html($value); ?>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endif; ?>
        <?php endforeach; ?>
    </table>
    <h2>Send test mail</h2>
    <?php if (isset($result) and $result === true): ?>
        <p><strong>Successfully sent</strong>, duration: <?php echo esc_html($duration); ?> second(s)</p>
    <?php elseif (isset($result) and $result === false): ?>
        <p>Error during sending.</p>
        <?php global $phpmailer; ?>
        <?php if ($phpmailer->ErrorInfo): ?>
            <?php echo esc_html($phpmailer->ErrorInfo); ?>
        <?php endif; ?>
    <?php endif; ?>
    <form method="post">
        <?php wp_nonce_field('smtp-configuration-test-mail'); ?>
        <table class="form-table">
            <tbody>
                <tr>
                    <th>
                        <label for="from">From</label>
                    </th>
                    <td>
                        <input type="email" name="from" id="from" value="<?php echo esc_attr($from); ?>">
                    </td>
                </tr>
                <tr>
                    <th>
                        <label for="to">To</label>
                    </th>
                    <td>
                        <input type="email" name="to" id="to" value="<?php echo esc_attr($to); ?>">
                    </td>
                </tr>
            </tbody>
        </table>
        <p class="submit">
            <input type="submit" class="button button-primary" value="Send">
        </p>
    </form>
</div>