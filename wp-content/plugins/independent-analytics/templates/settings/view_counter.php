<div class="view-counter-settings settings-container">
    <form method="post" action="options.php">
        <?php settings_fields('iawp_view_counter_settings'); ?>
        <?php do_settings_sections('independent-analytics-view-counter-settings'); ?>
        <div class="shortcode-note">
            <p><?php esc_html_e('You can output the view counter in a custom location using the shortcode:', 'iawp'); ?></p>
            <p><code>[iawp_view_counter label="Views:" icon="1"]</code></p>
            <p><?php printf(
    esc_html__('Use %1$s to hide the icon and %2$s to hide the label.', 'iawp'),
    '<code>icon="0"</code>',
    '<code>label=""</code>'
); ?></p>
        </div>
        <?php submit_button(__('Save Settings', 'iawp'), 'iawp-button purple', 'save-view-counter-settings'); ?>
    </form>
</div>