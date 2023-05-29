<?php

namespace IAWP_SCOPED\IAWP;

use IAWP_SCOPED\IAWP\Date_Range\Relative_Date_Range;
use IAWP_SCOPED\IAWP\Statistics\Page_Statistics;
class Dashboard_Widget
{
    public function __construct()
    {
        \add_action('wp_dashboard_setup', [$this, 'add_dashboard_widget']);
    }
    public function add_dashboard_widget()
    {
        if (Migrations\Migration::is_migrating() || !Capability_Manager::can_view()) {
            return;
        }
        $url = \add_query_arg(['page' => 'independent-analytics'], \admin_url('admin.php'));
        \ob_start();
        ?>
        <span><?php 
        \esc_html_e('Analytics', 'iawp');
        ?></span>
        <span>
            <a href="<?php 
        echo \esc_url($url);
        ?>" class="iawp-button purple">
                <?php 
        \esc_html_e('Open Dashboard');
        ?>
            </a>
        </span>
        <?php 
        $title = \ob_get_contents();
        \ob_end_clean();
        \wp_add_dashboard_widget('iawp', $title, [$this, 'dashboard_widget']);
    }
    public function dashboard_widget()
    {
        $date_range = new Relative_Date_Range('LAST_THIRTY');
        $statistics = new Page_Statistics($date_range);
        $chart = new Chart($statistics, null, \true);
        $stats = new Quick_Stats($statistics, null, \true);
        echo $chart->get_html();
        echo $stats->get_html();
    }
}
