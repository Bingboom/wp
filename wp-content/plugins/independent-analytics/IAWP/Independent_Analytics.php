<?php

namespace IAWP_SCOPED\IAWP;

use IAWP_SCOPED\IAWP\AJAX\Cleared_Cache_AJAX;
use IAWP_SCOPED\IAWP\AJAX\Create_Campaign_AJAX;
use IAWP_SCOPED\IAWP\AJAX\Delete_Data_AJAX;
use IAWP_SCOPED\IAWP\AJAX\Export_Campaigns_AJAX;
use IAWP_SCOPED\IAWP\AJAX\Export_Geo_AJAX;
use IAWP_SCOPED\IAWP\AJAX\Export_Referrers_AJAX;
use IAWP_SCOPED\IAWP\AJAX\Export_Views_AJAX;
use IAWP_SCOPED\IAWP\AJAX\Filters_AJAX;
use IAWP_SCOPED\IAWP\AJAX\Migration_Status_AJAX;
use IAWP_SCOPED\IAWP\AJAX\Real_Time_AJAX;
use IAWP_SCOPED\IAWP\AJAX\Test_Email_AJAX;
use IAWP_SCOPED\IAWP\AJAX\Update_Capabilities_AJAX;
use IAWP_SCOPED\IAWP\Queries\Country_Statistics;
use IAWP_SCOPED\IAWP\Statistics\Campaign_Statistics;
use IAWP_SCOPED\IAWP\Statistics\Geo_Statistics;
use IAWP_SCOPED\IAWP\Statistics\Page_Statistics;
use IAWP_SCOPED\IAWP\Statistics\Referrer_Statistics;
use IAWP_SCOPED\IAWP\Tables\Table_Campaigns;
use IAWP_SCOPED\IAWP\Tables\Table_Geo;
use IAWP_SCOPED\IAWP\Tables\Table_Referrers;
use IAWP_SCOPED\IAWP\Tables\Table_Pages;
use IAWP_SCOPED\IAWP\Utils\Security;
use IAWP_SCOPED\IAWP\Utils\Singleton;
class Independent_Analytics
{
    use Singleton;
    public $settings;
    public $email_reports;
    // This is where we attach functions to WP hooks
    private function __construct()
    {
        $this->settings = new Settings();
        new REST_API();
        new Dashboard_Widget();
        new View_Counter();
        new Delete_Data_AJAX();
        new Filters_AJAX();
        new Export_Geo_AJAX();
        new Export_Referrers_AJAX();
        new Export_Views_AJAX();
        new Migration_Status_AJAX();
        new Update_Capabilities_AJAX();
        new Cleared_Cache_AJAX();
        new Track_Resource_Changes();
        WooCommerce_Order::initialize_order_tracker();
        if (\IAWP_SCOPED\iawp_is_pro()) {
            $this->email_reports = new Email_Reports();
            new Export_Campaigns_AJAX();
            new Create_Campaign_AJAX();
            new Campaign_Builder();
            new Real_Time_AJAX();
            new Test_Email_AJAX();
        }
        \add_filter('admin_body_class', function ($classes) {
            if (\get_option('iawp_dark_mode')) {
                $classes .= ' iawp-dark-mode ';
            }
            return $classes;
        });
        \add_action('admin_menu', [$this, 'add_settings_page']);
        \add_action('admin_enqueue_scripts', [$this, 'enqueue_scripts']);
        \add_filter('plugin_action_links_independent-analytics/iawp.php', [$this, 'plugin_action_links']);
        \add_filter('admin_footer_text', [$this, 'ip_db_attribution'], 1, 1);
        \add_filter('admin_head', [$this, 'style_premium_menu_item']);
        \add_action('init', [$this, 'load_textdomain']);
        \add_action('init', [$this, 'polylang_translations']);
        IAWP_FS()->add_filter('connect_message_on_update', [$this, 'filter_connect_message_on_update'], 10, 6);
        IAWP_FS()->add_filter('connect_message', [$this, 'filter_connect_message_on_update'], 10, 6);
        IAWP_FS()->add_filter('is_submenu_visible', [$this, 'hide_freemius_sub_menus'], 10, 2);
        IAWP_FS()->add_filter('pricing_url', [$this, 'change_freemius_pricing_url'], 10);
    }
    // Text domain is used for i18n
    public function load_textdomain()
    {
        \load_plugin_textdomain('iawp', \false, \IAWP_LANGUAGES_DIRECTORY);
    }
    public function polylang_translations()
    {
        if (\function_exists('IAWP_SCOPED\\pll_register_string')) {
            pll_register_string('view_counter', 'Views:', 'Independent Analytics');
        }
    }
    // Settings page where the analytics will appear
    public function add_settings_page()
    {
        \add_menu_page('Independent Analytics', \esc_html__('Analytics', 'iawp'), Capability_Manager::can_view_string(), 'independent-analytics', [$this, 'settings_page_markup'], 'dashicons-analytics', 3);
        if (!Capability_Manager::white_labeled()) {
            \add_submenu_page('independent-analytics', \esc_html__('Feedback', 'iawp'), \esc_html__('Feedback', 'iawp'), Capability_Manager::can_view_string(), \esc_url('https://feedback.independentwp.com/boards/feature-requests'));
        }
        if (\IAWP_SCOPED\iawp_is_free() && !Capability_Manager::white_labeled()) {
            \add_submenu_page('independent-analytics', \esc_html__('Upgrade to Pro &rarr;', 'iawp'), \esc_html__('Upgrade to Pro &rarr;', 'iawp'), Capability_Manager::can_view_string(), \esc_url('https://independentwp.com/pricing/?utm_source=User+Dashboard&utm_medium=WP+Admin&utm_campaign=Upgrade+to+Pro&utm_content=Sidebar'));
        }
    }
    public function hide_freemius_sub_menus($is_visible, $menu_id)
    {
        if ('pricing' == $menu_id) {
            return \false;
        } elseif ('support' == $menu_id && Capability_Manager::white_labeled()) {
            return \false;
        } else {
            return \true;
        }
    }
    public function change_freemius_pricing_url()
    {
        return 'https://independentwp.com/pricing/?utm_source=User+Dashboard&utm_medium=WP+Admin&utm_campaign=Upgrade+to+Pro&utm_content=Account';
    }
    // The submenu item needs to be styled on all admin pages, and we only load stylesheets on our page
    public function style_premium_menu_item()
    {
        if (\IAWP_SCOPED\iawp_is_free()) {
            echo '<style>#toplevel_page_independent-analytics .wp-submenu li:nth-child(4) a { color: #F69D0A; }</style>';
        }
    }
    private function get_current_tab()
    {
        if (\IAWP_SCOPED\iawp_is_pro()) {
            $valid_tabs = ['views', 'referrers', 'geo', 'campaigns', 'campaign-builder', 'real-time', 'settings', 'learn'];
        } else {
            $valid_tabs = ['views', 'referrers', 'geo', 'settings', 'learn'];
        }
        $default_tab = $valid_tabs[0];
        $tab = \array_key_exists('tab', $_GET) ? \sanitize_text_field($_GET['tab']) : \false;
        $is_valid = \array_search($tab, $valid_tabs) != \false;
        if (!$tab || !$is_valid) {
            $tab = $default_tab;
        }
        return $tab;
    }
    public function settings_page_markup()
    {
        if (!Capability_Manager::can_view()) {
            return;
        }
        // ray(Migrations\Migration::has_newer_database_version());
        if (Migrations\Migration::has_newer_database_version()) {
            ?>
            <div id="iawp-parent" class="iawp-parent">
                <?php 
            echo \IAWP_SCOPED\iawp()->templates()->render('layout/header', ['show_review' => \false]);
            ?>
                <?php 
            echo \IAWP_SCOPED\iawp()->templates()->render('newer_database');
            ?>
            </div>
            <?php 
            return;
        } elseif (Migrations\Migration::is_migrating()) {
            ?>
            <div id="iawp-parent" class="iawp-parent">
                <?php 
            echo \IAWP_SCOPED\iawp()->templates()->render('layout/header', ['show_review' => \false]);
            ?>
                <?php 
            echo \IAWP_SCOPED\iawp()->templates()->render('migration_running');
            ?>
            </div>
            <?php 
            return;
        }
        $tab = $this->get_current_tab();
        ?>
        <div id="iawp-parent" class="iawp-parent">
            <?php 
        if (!Capability_Manager::white_labeled()) {
            echo \IAWP_SCOPED\iawp()->templates()->render('layout/header');
        }
        ?>
            <nav class="nav">
                <ul class="menu">
                    <li class="menu-item">
                        <a href="?page=independent-analytics"
                           data-controller="track-date-range"
                           data-test-menu="views"
                           class="menu-link link-dark <?php 
        if ($tab === 'views') {
            ?>active<?php 
        }
        ?>">
                            <?php 
        \esc_html_e('Pages', 'iawp');
        ?>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a href="?page=independent-analytics&tab=referrers"
                           data-controller="track-date-range"
                           data-test-menu="referrers"
                           class="menu-link link-dark <?php 
        if ($tab === 'referrers') {
            ?>active<?php 
        }
        ?>">
                            <?php 
        \esc_html_e('Referrers', 'iawp');
        ?>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a href="?page=independent-analytics&tab=geo"
                           data-controller="track-date-range"
                           data-test-menu="geo"
                           class="menu-link link-dark <?php 
        if ($tab === 'geo') {
            ?>active<?php 
        }
        ?>">
                            <?php 
        \esc_html_e('Geographic', 'iawp');
        ?>
                        </a>
                    </li>
                    <?php 
        if (\IAWP_SCOPED\iawp_is_pro()) {
            ?>
                        <li class="menu-item">
                            <a href="?page=independent-analytics&tab=campaigns"
                               data-controller="track-date-range"
                               data-test-menu="campaigns"
                               class="menu-link link-dark <?php 
            if ($tab === 'campaigns' || $tab === 'campaign-builder') {
                ?>active<?php 
            }
            ?>">
                                <?php 
            \esc_html_e('Campaigns', 'iawp');
            ?>
                            </a>
                            <div class="sub-menu">
                                <ul>
                                    <li class="menu-item">
                                        <a href="?page=independent-analytics&tab=campaign-builder"
                                           class="menu-link link-dark">
                                            <?php 
            \esc_html_e('Campaign Builder', 'iawp');
            ?>
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </li>
                        <li class="menu-item">
                            <a href="?page=independent-analytics&tab=real-time"
                               data-test-menu="real-time"
                               class="menu-link link-dark <?php 
            if ($tab === 'real-time') {
                ?>active<?php 
            }
            ?>">
                                <?php 
            \esc_html_e('Real-time', 'iawp');
            ?>
                            </a>
                        </li>
                    <?php 
        }
        ?>
                    <?php 
        if (Capability_Manager::can_edit()) {
            ?>
                        <li class="menu-item">
                            <a href="?page=independent-analytics&tab=settings"
                               data-test-menu="settings"
                               class="menu-link link-dark <?php 
            if ($tab === 'settings') {
                ?>active<?php 
            }
            ?>">
                                <?php 
            \esc_html_e('Settings', 'iawp');
            ?>
                            </a>
                        </li>
                    <?php 
        }
        ?>
                    <?php 
        if (!Capability_Manager::white_labeled()) {
            ?>
                        <li class="menu-item">
                            <a href="?page=independent-analytics&tab=learn"
                               data-test-menu="learn"
                               class="menu-link link-dark <?php 
            if ($tab === 'learn') {
                ?>active<?php 
            }
            ?>">
                                <?php 
            \esc_html_e('Learn', 'iawp');
            ?>
                            </a>
                        </li>
                    <?php 
        }
        ?>
                    <?php 
        if (!Capability_Manager::white_labeled() && \IAWP_SCOPED\iawp_is_free()) {
            ?>
                        <li class="menu-item upgrade first" data-controller="modal">
                            <a href="#"
                               data-test-menu="campaigns"
                               data-action="modal#toggleModal"
                               data-modal-target="modalButton"
                               class="menu-link link-dark">
                                <?php 
            \esc_html_e('Campaigns', 'iawp');
            ?>
                            </a>
                            <div class="upgrade-popup"
                                 data-modal-target="modal"
                            >
                                <div class="title">
                                    <span class="name"><?php 
            \esc_html_e('Campaigns', 'iawp');
            ?></span>
                                    <span class="iawp-label"><?php 
            \esc_html_e('PRO', 'iawp');
            ?></span>
                                </div>
                                <div class="description">
                                    <p><?php 
            \esc_html_e("Campaigns let you track visits from individual links you share online, whether that's a Tweet, ad, or guest post.", 'iawp');
            ?></p>
                                    <a class="iawp-button purple" target="_blank"
                                       href="https://independentwp.com/features/campaigns/?utm_source=User+Dashboard&utm_medium=WP+Admin&utm_campaign=Campaigns+menu+item&utm_content=Menu+item"><?php 
            \esc_html_e('Upgrade', 'iawp');
            ?></a>
                                </div>
                            </div>
                        </li>
                        <li class="menu-item upgrade" data-controller="modal">
                            <a href="#"
                               data-test-menu="real-time"
                               data-action="modal#toggleModal"
                               data-modal-target="modalButton"
                               class="menu-link link-dark">
                                <?php 
            \esc_html_e('Real-time', 'iawp');
            ?>
                            </a>
                            <div class="upgrade-popup"
                                 data-modal-target="modal"
                            >
                                <div class="title">
                                    <span class="name"><?php 
            \esc_html_e('Real-time analytics', 'iawp');
            ?></span>
                                    <span class="iawp-label"><?php 
            \esc_html_e('PRO', 'iawp');
            ?></span>
                                </div>
                                <div class="description">
                                    <p><?php 
            \esc_html_e("Real-time analytics let you see how many people are on your site right now, what pages they're viewing, and where they came from.", 'iawp');
            ?></p>
                                    <a class="iawp-button purple" target="_blank"
                                       href="https://independentwp.com/features/real-time/?utm_source=User+Dashboard&utm_medium=WP+Admin&utm_campaign=Real-time+menu+item&utm_content=Menu+item"><?php 
            \esc_html_e('Upgrade', 'iawp');
            ?></a>
                                </div>
                            </div>
                        </li>
                    <?php 
        }
        ?>
                </ul>
            </nav>
            <div class="iawp-tab-content"><?php 
        $options = new Dashboard_Options();
        $date_rage = $options->get_date_range();
        $date_label = $date_rage->label();
        $columns = $options->columns();
        if ($tab == 'views') {
            $table = new Table_Pages($columns);
            $statistics = new Page_Statistics($date_rage);
            $stats = new Quick_Stats($statistics, null);
            $chart = new Chart($statistics, $date_label);
            $this->interface($table, $stats, $chart);
        } elseif ($tab == 'referrers') {
            $table = new Table_Referrers($columns);
            $statistics = new Referrer_Statistics($date_rage);
            $stats = new Quick_Stats($statistics, null);
            $chart = new Chart($statistics, $date_label);
            $this->interface($table, $stats, $chart);
        } elseif ($tab == 'geo') {
            $table = new Table_Geo($columns);
            $statistics = new Geo_Statistics($date_rage);
            $stats = new Quick_Stats($statistics, null);
            $country_statistics = new Country_Statistics($date_rage);
            $chart = new Chart_Geo($country_statistics->fetch(), $date_label);
            $this->interface($table, $stats, $chart);
        } elseif ($tab === 'campaigns') {
            $table = new Table_Campaigns($columns);
            $statistics = new Campaign_Statistics($date_rage);
            $stats = new Quick_Stats($statistics, null);
            $chart = new Chart($statistics, $date_label);
            $this->interface($table, $stats, $chart);
        } elseif ($tab == 'campaign-builder') {
            (new Campaign_Builder())->render_campaign_builder();
        } elseif ($tab === 'real-time') {
            (new Real_Time())->render_real_time_analytics();
        } elseif ($tab == 'settings') {
            echo '<div id="iawp-dashboard" class="iawp-dashboard">';
            if (Capability_Manager::can_edit()) {
                $this->settings->render_settings();
            } else {
                echo '<p class="permission-blocked">' . \esc_html__('You do not have permission to edit the settings.', 'iawp') . '</p>';
            }
            echo '</div>';
        } elseif ($tab == 'learn') {
            echo '<div id="iawp-dashboard" class="iawp-dashboard">';
            echo \IAWP_SCOPED\iawp()->templates()->render('learn/index');
            echo '</div>';
        }
        ?>
            </div>
            <div id="loading-icon" class="loading-icon"><img
                        src="<?php 
        echo \esc_url(\IAWP_SCOPED\iawp_url_to('img/loading.svg'));
        ?>"/>
            </div>
            <button id="scroll-to-top" class="scroll-to-top"><span
                        class="dashicons dashicons-arrow-up-alt"></span>
            </button>
        </div>
        <?php 
    }
    public function interface($table, $stats, $chart)
    {
        $opts = new Dashboard_Options();
        ?>
        <div id="iawp-dashboard"
             class="iawp-dashboard"
             data-controller="report"
             data-report-relative-range-id-value="<?php 
        echo Security::attr($opts->relative_range_id());
        ?>"
             data-report-exact-start-value="<?php 
        echo Security::attr($opts->start());
        ?>"
             data-report-exact-end-value="<?php 
        echo Security::attr($opts->end());
        ?>"
             data-report-columns-value="<?php 
        \esc_attr_e(Security::json_encode($table->visible_column_ids()));
        ?>"
        >
            <?php 
        $table->output_toolbar();
        ?>
            <?php 
        echo $stats->get_html();
        ?>
            <?php 
        echo $chart->get_html();
        ?>
            <?php 
        echo $table->get_table_markup();
        ?>
        </div>
        <div class="iawp-notices"><?php 
        $health_check = new Health_Check();
        if (!$health_check->healthy()) {
            echo \IAWP_SCOPED\iawp()->templates()->render('settings/notice', ['notice_text' => $health_check->error(), 'button_text' => \false, 'notice' => 'iawp-error', 'url' => 'https://independentwp.com/knowledgebase/common-questions/views-not-recording/']);
        }
        if (\get_option('iawp_need_clear_cache')) {
            echo \IAWP_SCOPED\iawp()->templates()->render('settings/notice', ['notice_text' => \__('Please clear your cache to ensure tracking works properly.', 'iawp'), 'button_text' => \__('I\'ve cleared the cache', 'iawp'), 'notice' => 'iawp-warning', 'url' => 'https://independentwp.com/knowledgebase/common-questions/views-not-recording/']);
        }
        ?>
        </div><?php 
    }
    public function enqueue_scripts($hook)
    {
        if ($hook == 'toplevel_page_independent-analytics') {
            $tab = $this->get_current_tab();
            \wp_register_style('iawp-style', \IAWP_SCOPED\iawp_url_to('dist/styles/style.css'), [], \IAWP_VERSION);
            \wp_enqueue_style('iawp-style');
            \wp_register_script('iawp-js', \IAWP_SCOPED\iawp_url_to('dist/js/index.js'), [], \IAWP_VERSION);
            \wp_enqueue_script('iawp-js');
            \wp_add_inline_script('iawp-js', 'const IAWP_DELETE_DATA_NONCE = ' . \json_encode(\wp_create_nonce('iawp_delete_data')), 'before');
            if ($tab === 'views' || $tab === 'referrers' || $tab === 'geo' || $tab === 'campaigns' || $tab === 'campaign-builder') {
                \wp_register_script('iawp-data-table', \IAWP_SCOPED\iawp_url_to('dist/js/data-table.js'), [], \IAWP_VERSION);
                \wp_enqueue_script('iawp-data-table');
                \wp_add_inline_script('iawp-data-table', 'const IAWP_AJAX = ' . \json_encode(['filter_nonce' => \wp_create_nonce('iawp_filter'), 'create_campaign_nonce' => \wp_create_nonce('iawp_create_campaign'), 'copied_text' => \esc_html__('Copied!', 'iawp'), 'iawp_need_clear_cache_nonce' => \wp_create_nonce('iawp_need_clear_cache')]), 'before');
            } elseif ($tab == 'settings') {
                \wp_register_script('iawp-settings', \IAWP_SCOPED\iawp_url_to('dist/js/settings.js'), [], \IAWP_VERSION);
                \wp_enqueue_script('iawp-settings');
                \wp_add_inline_script('iawp-settings', 'const IAWP_AJAX = ' . \json_encode(['export_views_nonce' => \wp_create_nonce('iawp_export_views'), 'export_referrers_nonce' => \wp_create_nonce('iawp_export_referrers'), 'export_geo_nonce' => \wp_create_nonce('iawp_export_geo'), 'export_campaigns_nonce' => \wp_create_nonce('iawp_export_campaigns'), 'iawp_capabilities_nonce' => \wp_create_nonce('iawp_capabilities'), 'iawp_test_email_nonce' => \wp_create_nonce('iawp_test_email'), 'exporting_views_text' => \esc_html__('Exporting Pages...', 'iawp'), 'export_views_text' => \esc_html__('Export Pages', 'iawp'), 'exporting_referrers_text' => \esc_html__('Exporting Referrers...', 'iawp'), 'export_referrers_text' => \esc_html__('Export Referrers', 'iawp'), 'exporting_geo_text' => \esc_html__('Exporting Geolocations...', 'iawp'), 'export_geo_text' => \esc_html__('Export Geolocations', 'iawp'), 'exporting_campaigns_text' => \esc_html__('Exporting Campaigns...', 'iawp'), 'export_campaigns_text' => \esc_html__('Export Campaigns', 'iawp')]), 'before');
            } elseif ($tab == 'learn') {
                \wp_register_script('iawp-learn', \IAWP_SCOPED\iawp_url_to('dist/js/learn.js'), [], \IAWP_VERSION);
                \wp_enqueue_script('iawp-learn');
            }
        } elseif ($hook == 'index.php') {
            \wp_register_script('iawp-dashboard-widget', \IAWP_SCOPED\iawp_url_to('dist/js/dashboard_widget.js'), [], \IAWP_VERSION);
            \wp_enqueue_script('iawp-dashboard-widget');
            \wp_register_style('iawp-dashboard-widget-css', \IAWP_SCOPED\iawp_url_to('dist/styles/dashboard_widget.css'), [], \IAWP_VERSION);
            \wp_enqueue_style('iawp-dashboard-widget-css');
        }
    }
    /* WP uses $default when get_option() is empty, but it also saves empty fields
     ** as empty strings, meaning the return value can be "" instead of the default. This is
     ** a defensive function that guarantees we get the proper default when the setting is empty. */
    public function get_option($name, $default)
    {
        $option = \get_option($name, $default);
        return $option === '' ? $default : $option;
    }
    public function templates()
    {
        return new \IAWP_SCOPED\League\Plates\Engine(\IAWP_SCOPED\iawp_path_to('templates'));
    }
    public function get_users_can_write()
    {
        $roles = [];
        foreach (\wp_roles()->roles as $role_name => $role_obj) {
            if (!empty($role_obj['capabilities']['edit_posts'])) {
                $roles[] = $role_name;
            }
        }
        $users = \get_users(['role__in' => $roles]);
        return $users;
    }
    public function get_custom_types(bool $tax = \false)
    {
        $args = ['public' => \true, '_builtin' => \false];
        if ($tax) {
            return \get_taxonomies($args);
        } else {
            return \get_post_types($args);
        }
    }
    public function filter_connect_message_on_update($message, $user_first_name, $product_title, $user_login, $site_link, $freemius_link)
    {
        // Add the heading HTML.
        $plugin_name = 'Independent Analytics';
        $title = '<h3>' . \sprintf(\esc_html__('We hope you love %1$s', 'iawp'), $plugin_name) . '</h3>';
        $html = '';
        // Add the introduction HTML.
        $html .= '<p>';
        $html .= \sprintf(\esc_html__('Hi, %1$s! This is an invitation to help the %2$s community. ', 'iawp'), $user_first_name, $plugin_name);
        $html .= '<strong>';
        $html .= \sprintf(\esc_html__('If you opt-in, some data about your usage of %2$s will be shared with us', 'iawp'), $user_first_name, $plugin_name);
        $html .= '</strong>';
        $html .= \sprintf(\esc_html__(' so we can improve %2$s. We will also share some helpful info on using the plugin so you can get the most out of your sites analytics.', 'iawp'), $user_first_name, $plugin_name);
        $html .= '</p>';
        $html .= '<p>';
        $html .= \sprintf(\esc_html__('And if you skip this, that\'s okay! %1$s will still work just fine.', 'iawp'), $plugin_name);
        $html .= '</p>';
        return $title . $html;
    }
    public function plugin_action_links($links)
    {
        // Build the URL
        $url = \add_query_arg('page', 'independent-analytics', \admin_url('admin.php'));
        // Create the link
        $settings_link = '<a class="calendar-link" href="' . \esc_url($url) . '">' . \esc_html__('Analytics Dashboard', 'iawp') . '</a>';
        // Add the link to the start of the array
        \array_unshift($links, $settings_link);
        return $links;
    }
    public function set_time_limit($limit = 0)
    {
        if (!\function_exists('set_time_limit') && \false !== \strpos(\ini_get('disable_functions'), 'set_time_limit') && \ini_get('safe_mode')) {
            return \false;
        }
        return @\set_time_limit($limit);
    }
    public function ip_db_attribution($text)
    {
        if ($this->get_current_tab() === 'geo') {
            $text = $text . ' ' . \esc_html_x('Geolocation data powered by', 'Following text is a noun: DB-IP', 'iawp') . ' ' . '<a href="https://db-ip.com" class="geo-message" target="_blank">DB-IP</a>.';
        }
        return $text;
    }
    public function pagination_page_size()
    {
        return 50;
    }
}
