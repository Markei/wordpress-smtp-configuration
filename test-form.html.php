<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    <h2>Configuration</h2>
    <table class="wp-list-table widefat fixed striped table-view-list">
        <?php foreach (markei_wordpress_smtp__parse_settings() as $setting => $value): ?>
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
                                            <?php if ($option !== 'password'): ?>
                                                <?php echo esc_html($optionValue); ?>
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
    <?php if (strlen($flash) > 0): ?>
        <strong><?php echo esc_html($flash); ?></strong>
    <?php endif; ?>
    <form method="post">
        <table class="form-table">
            <tbody>
                <tr>
                    <th>
                        <label for="from">From</label>
                    </th>
                    <td>
                        <input type="email" name="from" id="from">
                    </td>
                </tr>
                <tr>
                    <th>
                        <label for="from">To</label>
                    </th>
                    <td>
                        <input type="email" name="to" id="to">
                    </td>
                </tr>
            </tbody>
        </table>
        <p class="submit">
            <input type="submit" class="button button-primary" value="Send">
        </p>
    </form>
</div>