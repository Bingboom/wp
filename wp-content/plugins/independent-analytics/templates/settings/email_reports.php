<div class="email-reports settings-container">
    <h2><?php esc_html_e('Email Report', 'iawp'); ?></h2>
    <div class="pro-tag"><?php esc_html_e('Pro', 'iawp'); ?></div>
    <p><?php esc_html_e('Schedule an automated email report for the 1st of every month.', 'iawp'); ?></p>
    <form method='post' action='options.php' id="email-reports-form" class="email-reports-form">
        <input type='hidden' name='option_page' value='iawp_email_report_settings'/>
        <input type="hidden" name="action" value="update"/>
        <input type="hidden" name="_wp_http_referer"
               value="/wp-admin/admin.php?page=independent-analytics&tab=settings">
        <?php wp_nonce_field('iawp_email_report_settings-options'); ?>
        <div class="inner">
            <div class="delivery-time section">
                <h3><?php esc_html_e('Delivery Time', 'iawp'); ?></h3>
                <select id="iawp_email_report_time" name="iawp_email_report_time">
                    <?php for ($i = 0; $i < 24; $i++) {
                        $readable_time = new DateTime(date('Y-m-d') . ' ' . $i . ':00:00');
                        $readable_time = $readable_time->format(get_option('time_format')); ?>
                        <option value="<?php esc_attr_e($i); ?>" <?php selected($time, $i, true); ?>><?php esc_html_e($readable_time); ?></option>
                    <?php
                    } ?>
                </select>
            </div>
            <div class="message section">
                <h3><?php esc_html_e('Email message', 'iawp'); ?></h3>
                <textarea rows="5" id="iawp_email_report_message" name="iawp_email_report_message"><?php esc_html_e($message); ?></textarea>
            </div>
            <div class="email-addresses section">
                <h3><?php esc_html_e('Add new email addresses', 'iawp'); ?></h3>
                <div class="new-address duplicator">
                    <div class="entry">
                        <input class="new-field" type="email" placeholder="name@email.com" value="" />
                        <button class="iawp-button purple duplicate-button"><?php esc_html_e('Add email', 'iawp'); ?></button>
                    </div>
                    <div class="blueprint">
                        <div class="entry">
                            <input type="text" readonly 
                                name="iawp_email_report_email_addresses[]" 
                                id="iawp_email_report_email_addresses[]" 
                                data-option="iawp_email_report_email_addresses" 
                                value="">
                            <button class="remove iawp-button ghost-purple"><?php esc_html_e('Remove email', 'iawp'); ?></button>
                        </div>
                    </div>
                    <p class="error-message empty"><?php esc_html_e('Input is empty', 'iawp'); ?></p>
                    <p class="error-message exists"><?php esc_html_e('This email already exists', 'iawp'); ?></p>
                </div>
                <div class="saved">
                    <h3><?php esc_html_e('Sending to these addresses', 'iawp'); ?></h3>
                    <?php for ($i = 0; $i < count($emails); $i++) : ?>
                        <div class="entry">
                            <input type="email" readonly
                                id="iawp_email_report_email_addresses[<?php esc_attr_e($i); ?>]" 
                                name="iawp_email_report_email_addresses[<?php esc_attr_e($i); ?>]" 
                                data-option="iawp_email_report_email_addresses"
                                value="<?php esc_attr_e($emails[$i]); ?>" />
                                <button class="remove iawp-button ghost-purple"><?php esc_html_e('Remove email', 'iawp'); ?></button>
                        </div>
                    <?php endfor; ?>
                </div>
            </div>
            <div class="save-button-container">
                <?php submit_button(esc_html__('Save settings', 'iawp'), 'iawp-button purple', 'save-email-report-settings', false); ?>
                <button id="test-email" class="test-email iawp-button ghost-purple"><?php esc_html_e('Send test email', 'iawp'); ?></button>
                <p class="warning-message"><span class="dashicons dashicons-warning"></span> <?php esc_html_e('Unsaved changes', 'iawp'); ?></p>
            </div>
        </div>
    </form>
</div>