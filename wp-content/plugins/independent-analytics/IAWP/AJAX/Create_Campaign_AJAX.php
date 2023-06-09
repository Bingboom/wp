<?php

namespace IAWP_SCOPED\IAWP\AJAX;

use IAWP_SCOPED\IAWP\Campaign_Builder;
use IAWP_SCOPED\IAWP\Utils\Security;
class Create_Campaign_AJAX extends AJAX
{
    protected function action_name() : string
    {
        return 'iawp_create_campaign';
    }
    protected function action_callback() : void
    {
        $campaign_builder = new Campaign_Builder();
        $html = $campaign_builder->create_campaign(Security::string(\trim($this->get_field('path'))), Security::string(\trim($this->get_field('utm_source'))), Security::string(\trim($this->get_field('utm_medium'))), Security::string(\trim($this->get_field('utm_campaign'))), Security::string(\trim($this->get_field('utm_term'))), Security::string(\trim($this->get_field('utm_content'))));
        \wp_send_json_success(['html' => $html]);
    }
}
