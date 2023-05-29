<?php

namespace IAWP_SCOPED\IAWP;

use IAWP_SCOPED\IAWP\Statistics\Statistics;
use IAWP_SCOPED\IAWP\Utils\Security;
class Chart
{
    private $statistics;
    private $title;
    private $preview;
    public function __construct(Statistics $statistics, ?string $title, bool $preview = \false)
    {
        $this->preview = $preview;
        $this->statistics = $statistics;
        $this->title = $title;
    }
    public function get_html()
    {
        $labels = \array_map(function ($data_point) {
            return $data_point[0]->format('M j - l');
        }, $this->statistics->daily_views());
        $views_data = \array_map(function ($data_point) {
            return $data_point[1];
        }, $this->statistics->daily_views());
        $visitors_data = \array_map(function ($data_point) {
            return $data_point[1];
        }, $this->statistics->daily_visitors());
        $sessions_data = \array_map(function ($data_point) {
            return $data_point[1];
        }, $this->statistics->daily_sessions());
        $woocommerce_orders_data = \array_map(function ($data_point) {
            return $data_point[1];
        }, $this->statistics->daily_woocommerce_orders());
        $woocommerce_net_sales_data = \array_map(function ($data_point) {
            return $data_point[1];
        }, $this->statistics->daily_woocommerce_net_sales());
        return $this->chart_html($labels, $views_data, $visitors_data, $sessions_data, $woocommerce_orders_data, $woocommerce_net_sales_data);
    }
    private function chart_html(array $labels, array $views, array $visitors, array $sessions, array $woocommerce_orders_data, array $woocommerce_net_sales_data)
    {
        \ob_start();
        ?>
        <div class="chart-container">
        <div class="chart-inner">
            <div class="legend-container">
                <h2 class="legend-title"><?php 
        \esc_html_e($this->title);
        ?></h2>
                <div class="legend"></div>
            </div>
            <canvas id="myChart"
                    width="800"
                    height="200"
                    data-controller="chart"
                    data-chart-preview-value='<?php 
        echo $this->is_preview() ? '1' : '0';
        ?>'
                    data-chart-using-woocommerce-value='<?php 
        echo \IAWP_SCOPED\iawp_using_woocommerce() ? '1' : '0';
        ?>'
                    data-chart-labels-value='<?php 
        echo Security::json_encode($labels);
        ?>'
                    data-chart-views-value='<?php 
        echo Security::json_encode($views);
        ?>'
                    data-chart-visitors-value='<?php 
        echo Security::json_encode($visitors);
        ?>'
                <?php 
        if ($this->is_full_view()) {
            ?>
                    data-chart-sessions-value='<?php 
            echo Security::json_encode($sessions);
            ?>'
                <?php 
        }
        ?>
                <?php 
        if ($this->is_full_view() && \IAWP_SCOPED\iawp_using_woocommerce()) {
            ?>
                    data-chart-language-value="<?php 
            echo \get_bloginfo('language');
            ?>"
                    data-chart-currency-value="<?php 
            echo get_woocommerce_currency();
            ?>"
                    data-chart-woocommerce-orders-value='<?php 
            echo Security::json_encode($woocommerce_orders_data);
            ?>'
                    data-chart-woocommerce-net-sales-value='<?php 
            echo Security::json_encode($woocommerce_net_sales_data);
            ?>'
                <?php 
        }
        ?>
            >
            </canvas>
        </div>
        </div><?php 
        $html = \ob_get_contents();
        \ob_end_clean();
        return $html;
    }
    /**
     * @return bool
     */
    private function is_preview() : bool
    {
        return $this->preview;
    }
    /**
     * @return bool
     */
    private function is_full_view() : bool
    {
        return !$this->is_preview();
    }
}
