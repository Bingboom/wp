<?php

namespace IAWP_SCOPED\IAWP\AJAX;

use IAWP_SCOPED\IAWP\Capability_Manager;
use IAWP_SCOPED\IAWP\Migrations;
abstract class AJAX
{
    public function __construct()
    {
        \add_action('wp_ajax_' . $this->action_name(), [$this, 'intercept_ajax']);
    }
    /**
     * Classes must define an action name for the ajax request
     *
     * The required nonce for the ajax request will be the action name with a "_nonce" postfix
     * Example: "iawp_delete_data" require a "iawp_delete_data_nonce" nonce field
     *
     * @return string
     */
    protected abstract function action_name() : string;
    /**
     * Classes must define an action callback to run when an ajax request is made
     *
     * @return void
     */
    protected abstract function action_callback() : void;
    /**
     * Classes can define a set of required fields for an ajax request
     *
     * @return array
     */
    protected function action_required_fields() : array
    {
        return [];
    }
    /**
     * This is the direct handler for ajax requests.
     * Permissions and nonce values are checked before executing the ajax action_callback function.
     *
     * @return void
     */
    public final function intercept_ajax() : void
    {
        // Todo - Should this be can_edit() instead?
        $is_not_migrating = $this->allowed_during_migrations() || !Migrations\Migration::is_migrating();
        $valid_fields = !$this->missing_fields();
        $can_view = Capability_Manager::can_view();
        \check_ajax_referer($this->action_name(), $this->action_name() . '_nonce');
        if ($is_not_migrating && $valid_fields && $can_view) {
            $this->action_callback();
        } else {
            \wp_send_json_error(['errorMessage' => 'Unable to process IAWP AJAX request']);
        }
        \wp_die();
    }
    /**
     * Override method to allow the AJAX request to run during migrations
     *
     * @return bool false by default
     */
    protected function allowed_during_migrations() : bool
    {
        return \false;
    }
    /**
     * Get a field value. This method supports text and arrays. Returns array if no field found.
     *
     * @param $field_name
     * @return array|string|null
     */
    protected final function get_field($field_name)
    {
        if (!\array_key_exists($field_name, $_POST)) {
            return null;
        }
        $type = \gettype($_POST[$field_name]);
        if ($type == 'array') {
            return \rest_sanitize_array($_POST[$field_name]);
        } else {
            return \sanitize_text_field($_POST[$field_name]);
        }
    }
    private function missing_fields() : bool
    {
        foreach ($this->action_required_fields() as $required_field) {
            if (!\array_key_exists($required_field, $_POST)) {
                return \true;
            }
        }
        return \false;
    }
}
