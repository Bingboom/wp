<div class="blocked-ip-settings settings-container">
    <h2><?php esc_html_e('Block IP Addresses', 'iawp'); ?></h2>
    <p><?php esc_html_e('Enter an IP address to exclude it from tracking.', 'iawp'); ?> <a
                target="_blank"
                href="https://independentwp.com/knowledgebase/data/how-to-block-ip-addresses/">Learn
            more</a></p>
    <form method='post' action='options.php' id="block-ip-form" class="block-ip-form">
        <input type='hidden' name='option_page' value='iawp_blocked_ip_settings'/>
        <input type="hidden" name="action" value="update"/>
        <input type="hidden" name="_wp_http_referer"
               value="/wp-admin/admin.php?page=independent-analytics&tab=settings">
        <?php wp_nonce_field('iawp_blocked_ip_settings-options'); ?>
        <div class="inner">
            <div class="block-new-ip duplicator">
                <div class="entry">
                    <input class="new-field" type="text" placeholder="<?php esc_attr_e('76.98.172.122', 'iawp'); ?>" value="" />
                    <button class="iawp-button purple duplicate-button"><?php esc_html_e('Block New IP', 'iawp'); ?></button>
                </div>
                <div class="blueprint">
                    <div class="entry">
                        <input type="text" readonly 
                            name="iawp_blocked_ips[]" 
                            id="iawp_blocked_ips[]"
                            data-option="iawp_blocked_ips"
                            value="">
                        <button class="remove iawp-button ghost-purple"><?php esc_html_e('Remove IP', 'iawp'); ?></button>
                    </div>
                </div>
                <p class="error-message empty"><?php esc_html_e('Input is empty', 'iawp'); ?></p>
                <p class="error-message exists"><?php esc_html_e('This IP is already blocked', 'iawp'); ?></p>
            </div>
            <div class="saved">
                <h3><?php esc_html_e('Blocked IPs', 'iawp'); ?></h3>
                <?php for ($i = 0; $i < count($ips); $i++): ?>
                    <div class="entry">
                        <input type="text" readonly
                               name="iawp_blocked_ips[<?php esc_attr_e($i); ?>]"
                               id="iawp_blocked_ips[<?php esc_attr_e($i); ?>]"
                               data-option="iawp_blocked_ips"
                               value="<?php esc_attr_e($ips[$i]); ?>">
                        <button class="remove iawp-button ghost-purple"><?php esc_html_e('Remove IP', 'iawp'); ?></button>
                    </div>
                <?php endfor; ?>
                <?php if (count($ips) === 0): ?>
                    <p><?php esc_html_e('No blocked IPs', 'iawp'); ?></p>
                <?php endif; ?>
            </div>
            <div class="save-button-container">
                <?php submit_button(esc_html__('Save IP Addresses', 'iawp'), 'iawp-button purple', 'save-blocked-ip-settings', false); ?>
                <p class="warning-message"><span class="dashicons dashicons-warning"></span> <?php esc_html_e('Unsaved changes', 'iawp'); ?></p>
            </div>
        </div>
    </form>
</div>