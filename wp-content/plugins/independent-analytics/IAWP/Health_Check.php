<?php

namespace IAWP_SCOPED\IAWP;

class Health_Check
{
    private $error;
    public function __construct()
    {
        $this->error = $this->plugin_health_check();
    }
    /**
     * Did the health check pass?
     *
     * @return bool
     */
    public function healthy() : bool
    {
        return empty($this->error);
    }
    /**
     * Returns the health check error, if any
     *
     * @return string|null
     */
    public function error() : ?string
    {
        return $this->error;
    }
    /**
     * @return string|null Returns a string error message if the health check fails
     */
    private function plugin_health_check() : ?string
    {
        $active_plugins = \get_option('active_plugins');
        if (\in_array('disable-wp-rest-api/disable-wp-rest-api.php', $active_plugins)) {
            return \__('The "Disable WP REST API" plugin needs to be deactivated because Independent Analytics uses the REST API to record visits.', 'iawp');
        }
        if (\in_array('all-in-one-wp-security-and-firewall/wp-security.php', $active_plugins)) {
            $settings = \get_option('aio_wp_security_configs', []);
            if (\array_key_exists('aiowps_disallow_unauthorized_rest_requests', $settings)) {
                if ($settings['aiowps_disallow_unauthorized_rest_requests'] == 1) {
                    return \__('The "All In One WP Security" plugin is blocking REST API requests, which Independent Analytics needs to record views. Please disable this setting via the WP Security > Miscellaneous menu.', 'iawp');
                }
            }
        }
        if (\in_array('disable-json-api/disable-json-api.php', $active_plugins)) {
            $settings = \get_option('disable_rest_api_options', []);
            if (\array_key_exists('roles', $settings)) {
                if ($settings['roles']['none']['default_allow'] == \false) {
                    if ($settings['roles']['none']['allow_list']['/iawp/search'] == \false) {
                        return \__('The "Disable REST API" plugin is blocking REST API requests for unauthenticated users, which Independent Analytics needs to record views. Please enable the /iawp/search route, so Independent Analytics can track your visitors.', 'iawp');
                    }
                }
            }
        }
        if (\in_array('disable-xml-rpc-api/disable-xml-rpc-api.php', $active_plugins)) {
            $settings = \get_option('dsxmlrpc-settings');
            if (\array_key_exists('json-rest-api', $settings)) {
                if ($settings['json-rest-api'] == 1) {
                    return \__('The "Disable XML-RPC-API" plugin is blocking REST API requests, which Independent Analytics needs to record views. Please visit the Security Settings menu and turn off the "Disable JSON REST API" option, so Independent Analytics can track your visitors.', 'iawp');
                }
            }
        }
        if (\in_array('wpo-tweaks/wordpress-wpo-tweaks.php', $active_plugins)) {
            return \__('The "WPO Tweaks & Optimizations" plugin needs to be deactivated because it is disabling the REST API, which Independent Analytics uses to record visits.', 'iawp');
        }
        if (\in_array('all-in-one-intranet/basic_all_in_one_intranet.php', $active_plugins)) {
            return \__('The "All-In-One Intranet" plugin needs to be deactivated because it is disabling the REST API, which Independent Analytics uses to record visits. You may want to try the "My Private Site" plugin instead.', 'iawp');
        }
        if (\in_array('wp-security-hardening/wp-hardening.php', $active_plugins)) {
            $settings = \get_option('whp_fixer_option');
            if ($settings['disable_json_api']) {
                return \__('The "WP Hardening" plugin is blocking the REST API, which Independent Analytics needs to record views. Please visit the WP Hardening > Security Fixers menu and turn off the "Disable WP API JSON" option, so Independent Analytics can track your visitors.', 'iawp');
            }
        }
        if (\in_array('wp-rest-api-authentication/miniorange-api-authentication.php', $active_plugins)) {
            $settings = \get_option('mo_api_authentication_protectedrestapi_route_whitelist');
            if (\in_array('/iawp/search', $settings)) {
                return \__('The "WordPress REST API Authentication" plugin is blocking the REST API, which Independent Analytics needs to record views. Please visit the miniOrange API Authentication > Protected REST APIs menu and uncheck the "/iawp/search" box to allow Independent Analytics to track your visitors.', 'iawp');
            }
        }
        if (\in_array('perfmatters/perfmatters.php', $active_plugins)) {
            $settings = \get_option('perfmatters_options');
            if (\array_key_exists('disable_rest_api', $settings)) {
                if ($settings['disable_rest_api'] != '') {
                    return \__('The "Perfmatters" plugin is blocking the REST API, which Independent Analytics needs to record views. Please visit the Settings > Perfmatters menu and change the "Disable REST API" setting to "Default (enabled)" to allow Independent Analytics to track your visitors.', 'iawp');
                }
            }
        }
        return null;
    }
}