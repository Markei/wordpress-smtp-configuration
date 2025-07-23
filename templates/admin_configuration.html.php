<?php
    if (defined('ABSPATH') === false) {
        exit;
    }
?>
    <h2>Configuration</h2>
    <p>Configuration via: <?php echo $settings['configuredViaConst'] ? 'constant' : 'other'; ?>
    <?php if ($settings['settings']): ?>
        <table class="wp-list-table widefat fixed striped table-view-list">
            <?php foreach ($settings['settings'] as $name => $setting): ?>
                <tr>
                    <th><strong><?php echo esc_html($name); ?></strong></th>
                    <td>
                        <?php if ($setting['sensitive'] === true): ?>
                            ***
                        <?php elseif ($setting['type'] === 'array'): ?>
                            <?php echo esc_html(print_r($setting['value'], true)); ?>
                        <?php elseif ($setting['type'] === 'bool'): ?>
                            <?php echo $setting['value'] ? 'true' : 'false'; ?>
                        <?php else: ?>
                            <?php echo esc_html($setting['value']); ?>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php endif; ?>