<div class="settings-container"
     data-controller="migration-redirect"
     data-migration-redirect-nonce-value="<?php echo wp_create_nonce('iawp_migration_status') ?>"
>
    <h2><?php esc_html_e('Newer database version found', 'iawp'); ?></h2>
    <p>
        <?php esc_html_e("It looks like you've downgraded the version of Independent Analytics that you're using. Unfortunately, there no way to safely downgrade the database without data loss.", 'iawp'); ?>
    </p>
    <p>
        <?php esc_html_e('Please update to the latest version of Independent Analytics.', 'iawp'); ?>
    </p>
</div>