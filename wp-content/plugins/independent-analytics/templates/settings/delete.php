<div data-controller="delete-data" class="export-settings settings-container">
    <h2><?php esc_html_e('Danger zone', 'iawp') ?></h2>
    <div class="button-group">
        <button id="delete-everything-button" data-action="click->delete-data#open"
                class="iawp-button ghost-red">
            <?php esc_html_e('Delete all data', 'iawp'); ?>
        </button>
    </div>
    <div id="delete-data-modal" aria-hidden="true" class="mm micromodal-slide">
        <div tabindex="-1" class="mm__overlay" data-action="click->delete-data#close">
            <div role="dialog" aria-modal="true" aria-labelledby="delete-data-modal-title"
                 class="mm__container">
                <h1><?php esc_html_e('Delete all data', 'iawp'); ?></h1>
                <p>
                    <strong>
                        <?php echo sanitize_text_field($site_name); ?>
                        <br/>
                        <?php echo esc_url($site_url); ?>
                    </strong>
                </p>
                <p>
                    <?php esc_html_e('You are about to delete all analytics data associated with Independent Analytics. This includes all views, referrers, and settings.', 'iawp'); ?>
                </p>
                <p><?php printf(esc_html__('Type "%s" in the input below to confirm.', 'iawp'), 'Delete all data'); ?></p>
                <form data-action="submit->delete-data#submit">
                    <input type="text" autofocus data-delete-data-target="input"
                           data-action="input->delete-data#updateConfirmation" class="block-input">
                    <button type="submit" class="iawp-button purple"
                            data-delete-data-target="submit"><?php esc_html_e('Delete all data', 'iawp'); ?></button>
                    <button type="button" class="iawp-button ghost-purple"
                            data-micromodal-close><?php esc_html_e('Cancel', 'iawp'); ?></button>
                </form>
            </div>
        </div>
    </div>
</div>
