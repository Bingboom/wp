<div class="settings-container"
     data-controller="migration-redirect"
     data-migration-redirect-nonce-value="<?php echo wp_create_nonce('iawp_migration_status') ?>"
>
    <h2><?php esc_html_e('Update running', 'iawp'); ?></h2>
    <p>
        <?php esc_html_e("We're running an update designed to speed up and improve Independent Analytics.
        This can take anywhere from 30 seconds to 5 minutes.", 'iawp'); ?>
    </p>
    <p>
        <?php esc_html_e("Your site's performance is not impacted by this update. Analytics tracking will resume once the update is complete.", 'iawp'); ?>
    </p>
    <p>
        <strong><?php esc_html_e("This page will automatically refresh when the update's finished.", 'iawp'); ?></strong>
    </p>
    <p><span class="dashicons dashicons-update-alt spin"></span></p>
</div>