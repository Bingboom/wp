<?php
$new_campaign_url = $new_campaign_url ?? null;
$path = $path ?? null;
$path_error = $path_error ?? null;
$utm_source = $utm_source ?? null;
$utm_source_error = $utm_source_error ?? null;
$utm_medium = $utm_medium ?? null;
$utm_medium_error = $utm_medium_error ?? null;
$utm_campaign = $utm_campaign ?? null;
$utm_campaign_error = $utm_campaign_error ?? null;
$utm_term = $utm_term ?? null;
$utm_content = $utm_content ?? null;
?>

<div class="campaign-builder" data-controller="campaign-builder">
    <div class="settings-container">
        <div class="settings-container-header">
            <h2><?php esc_html_e('Campaign URL Builder', 'iawp'); ?></h2>
            <a class="link-purple"
               href="https://independentwp.com/knowledgebase/campaigns/how-to-campaign-builder"
               target="_blank"><?php esc_html_e('Learn how to create campaigns', 'iawp'); ?> <span
                        class="dashicons dashicons-external"></span></a>
        </div>
        <form action="" data-action="campaign-builder#submit" data-campaign-builder-target="form">
            <div class="table-container">
                <table class="form-table campaign-table" role="presentation">
                    <tbody>
                    <tr>
                        <th scope="row">
                            <label for="iawp_site_url"><?php esc_html_e('Site URL', 'iawp'); ?></label>
                        </th>
                        <td>
                            <input type="text"
                                   name="site_url"
                                   id="iawp_site_url"
                                   value="<?php echo trailingslashit(site_url()) ?>"
                                   disabled="disabled"
                            />
                            <p class="description"><?php esc_html_e('Campaign links always lead to your site', 'iawp'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="iawp_path"><?php esc_html_e('Landing Page Path', 'iawp'); ?></label>
                        </th>
                        <td>
                            <input type="text"
                                   name="path"
                                   id="iawp_path"
                                   placeholder="/blog/some-post"
                                   value="<?php esc_attr_e($path) ?>"
                            />
                            <p class="description"><?php esc_html_e('Leave empty to use your homepage', 'iawp'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="iawp_utm_source">Source <span
                                        class="required">*</span></label>
                        </th>
                        <td>
                            <input type="text"
                                   name="utm_source"
                                   id="iawp_utm_source"
                                   placeholder="Twitter"
                                   value="<?php esc_attr_e($utm_source); ?>"
                                   class="<?php echo isset($utm_source_error) ? 'error' : '' ?>"
                            />
                            <p class="description"><?php esc_html_e('Name of the website the link will be placed on', 'iawp'); ?></p>
                            <?php if (isset($utm_source_error)): ?>
                                <p class="error"><?php esc_html_e($utm_source_error) ?></p>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="iawp_utm_medium">Medium <span
                                        class="required">*</span></label>
                        </th>
                        <td>
                            <input type="text"
                                   name="utm_medium"
                                   id="iawp_utm_medium"
                                   placeholder="<?php esc_attr_e('Social Media', 'iawp'); ?>"
                                   value="<?php esc_attr_e($utm_medium) ?>"
                                   class="<?php echo isset($utm_medium_error) ? 'error' : '' ?>"
                            />
                            <p class="description"><?php esc_html_e('Type of website e.g. Search, Social, Ad', 'iawp'); ?></p>
                            <?php if (isset($utm_medium_error)): ?>
                                <p class="error"><?php esc_html_e($utm_medium_error) ?></p>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="iawp_utm_campaign">Campaign <span class="required">*</span></label>
                        </th>
                        <td>
                            <input type="text"
                                   name="utm_campaign"
                                   id="iawp_utm_campaign"
                                   placeholder="<?php esc_attr_e('5 Ways to Get More Traffic', 'iawp'); ?>"
                                   value="<?php esc_attr_e($utm_campaign) ?>"
                                   class="<?php echo isset($utm_campaign_error) ? 'error' : '' ?>"
                            />
                            <p class="description"><?php esc_html_e('Title of the page or ad', 'iawp'); ?></p>
                            <?php if (isset($utm_campaign_error)): ?>
                                <p class="error"><?php esc_html_e($utm_campaign_error) ?></p>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="iawp_utm_term">Term</label>
                        </th>
                        <td>
                            <input type="text"
                                   name="utm_term"
                                   id="iawp_utm_term"
                                   placeholder="<?php esc_attr_e('Get website traffic', 'iawp'); ?>"
                                   value="<?php esc_attr_e($utm_term) ?>"
                            />
                            <p class="description"><?php esc_html_e('Keyword used in paid advertising', 'iawp'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="iawp_utm_content">Content</label>
                        </th>
                        <td>
                            <input type="text"
                                   name="utm_content"
                                   id="iawp_utm_content"
                                   placeholder="<?php esc_attr_e('Bio link', 'iawp'); ?>"
                                   value="<?php esc_attr_e($utm_content) ?>"
                            />
                            <p class="description"><?php esc_html_e('Position of the link on the page e.g. author bio', 'iawp'); ?></p>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
            <div class="submit-container">
                <p class="submit">
                    <button class="button iawp-button purple"
                            data-campaign-builder-target="submitButton"
                    >
                        <span><?php esc_html_e('Create Campaign URL', 'iawp'); ?></span><span
                                class="dashicons dashicons-update"></span>
                    </button>
                </p>
            </div>
        </form>
        <?php if (isset($new_campaign_url)): ?>
            <div class="campaign new"
                 data-campaign-builder-target="newCampaign">
                <p class="campaign-title"><?php esc_html_e('New campaign created', 'iawp'); ?>
                    &#127881;</p>
                <div class="campaign-copy">
                    <div class="campaign-text">
                        <input readonly class="campaign-url"
                               data-controller="select-input"
                               data-action="click->select-input#selectInput"
                               value="<?php esc_attr_e($new_campaign_url); ?>"/>
                    </div>
                    <div class="campaign-actions">
                        <button class="iawp-button purple"
                                data-controller="clipboard"
                                data-action="clipboard#copy"
                                data-clipboard-text-value="<?php esc_attr_e($new_campaign_url); ?>"
                        >
                            <?php esc_html_e('Copy URL', 'iawp'); ?>
                        </button>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
    <div class="settings-container">
        <div class="campaigns-heading">
            <h2><?php esc_html_e('Latest Campaign URLs', 'iawp'); ?></h2>
        </div>
        <?php if (count($campaigns) === 0): ?>
            <p class="campaigns-empty"><?php esc_html_e('No campaign URLs found', 'iawp'); ?></p>
        <?php endif ?>
        <?php foreach ($campaigns as $campaign): ?>
            <div class="campaign">
                <div class="campaign-copy">
                    <div class="campaign-text">
                        <input readonly class="campaign-url"
                               data-controller="select-input"
                               data-action="click->select-input#selectInput"
                               value="<?php esc_attr_e($campaign['url']); ?>">
                        <p class="campaign-created-at">
                            <?php printf(esc_html_x('Created %s', 'Created five minutes ago', 'iawp'), esc_html__($campaign['created_at'])); ?>
                        </p>
                    </div>
                    <div class="campaign-actions">
                        <button class="iawp-button purple"
                                data-controller="clipboard"
                                data-action="clipboard#copy"
                                data-clipboard-text-value="<?php esc_attr_e($campaign['url']); ?>"
                        >
                            <?php esc_html_e('Copy URL', 'iawp'); ?>
                        </button>
                        <button class="iawp-button ghost-purple"
                                data-action="campaign-builder#reuse"
                                data-result="<?php esc_attr_e($campaign['result']) ?>"
                        >
                            <?php esc_html_e('Reuse', 'iawp'); ?>
                        </button>
                    </div>
                </div>
            </div>
        <?php endforeach ?>
    </div>
</div>
