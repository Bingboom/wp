<?php
$show_review = $show_review ?? true ?>

<div class="header">
    <div class="logo"><img src="<?php echo esc_url(iawp_url_to('img/logo.png')) ?>"/></div>
    <?php if (iawp_is_free()): ?>
        <div class="upgrade">
            <a href="https://independentwp.com/pricing/?utm_source=User+Dashboard&utm_medium=WP+Admin&utm_campaign=Upgrade+to+Pro&utm_content=Header" class="iawp-button" target="_blank">
                <span><?php esc_html_e('Upgrade now and save 45%', 'iawp'); ?></span>
                <span class="dashicons dashicons-arrow-right-alt"></span>
            </a>
        </div>
    <?php endif; ?>
    <div class="kb">
        <?php if ($show_review): ?>
            <a href="https://wordpress.org/support/plugin/independent-analytics/reviews/"
               class="iawp-button text review" target="_blank">
                <span><?php esc_html_e('Leave us a Review', 'iawp'); ?></span>
                <span class="dashicons dashicons-star-filled"></span>
            </a>
        <?php endif; ?>
        <a href="https://independentwp.com/knowledgebase/" class="iawp-button purple" target="_blank">
            <span><?php esc_html_e('Knowledge Base', 'iawp'); ?></span>
            <span class="dashicons dashicons-external"></span>
        </a>
    </div>
</div>