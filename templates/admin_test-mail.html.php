    <h2>Send test mail</h2>
    <?php if (isset($testMail['result']) and $testMail['result'] === true): ?>
        <p><strong>Successfully sent</strong>, duration: <?php echo esc_html($testMail['duration']); ?> second(s)</p>
    <?php elseif (isset($testMail['result']) and $testMail['result'] === false): ?>
        <p><strong>Error during sending.</strong></p>
        <?php if ($testMail['phpmailer']->ErrorInfo): ?>
            <?php echo esc_html($testMail['phpmailer']->ErrorInfo); ?>
        <?php endif; ?>
    <?php endif; ?>
    <form method="post" action="<?php echo esc_attr($adminUrl); ?>">
        <?php wp_nonce_field('markei-smtp-configuration___test-mail'); ?>
        <input type="hidden" name="act" value="test-mail">
        <table class="form-table">
            <tbody>
                <tr>
                    <th>
                        <label for="from">From</label>
                    </th>
                    <td>
                        <input type="email" name="from" id="from" value="<?php echo esc_attr($testMail['from']); ?>">
                    </td>
                </tr>
                <tr>
                    <th>
                        <label for="to">To</label>
                    </th>
                    <td>
                        <input type="email" name="to" id="to" value="<?php echo esc_attr($testMail['to']); ?>">
                    </td>
                </tr>
            </tbody>
        </table>
        <p class="submit">
            <input type="submit" class="button button-primary" value="Send">
        </p>
    </form>