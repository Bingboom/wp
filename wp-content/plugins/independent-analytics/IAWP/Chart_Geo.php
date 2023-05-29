<?php

namespace IAWP_SCOPED\IAWP;

class Chart_Geo
{
    private $geos;
    private $title;
    public function __construct($geos, $title = null)
    {
        $this->geos = $geos;
        $this->title = $title;
    }
    private function get_tooltip(Models\Geo $geo) : string
    {
        $geo->flag();
        \ob_start();
        ?>
        <div class="iawp-geo-chart-tooltip">
            <img class="flag" alt="Country flag" src="<?php 
        echo $geo->flag();
        ?>"/>
            <h1><?php 
        echo $geo->country();
        ?></h1>
            <p><span><?php 
        \_e('Views');
        ?>: </span> <?php 
        echo $geo->views();
        ?></p>
            <p><span><?php 
        \_e('Visitors');
        ?>: </span><?php 
        echo $geo->visitors();
        ?> </p>
            <p><span><?php 
        \_e('Sessions');
        ?>: </span><?php 
        echo $geo->sessions();
        ?> </p>
        </div>
        <?php 
        $html = \ob_get_contents();
        \ob_end_clean();
        return $html;
    }
    public function get_html()
    {
        $chart_data = \array_map(function ($geo) {
            return [$geo->country(), $geo->views(), $this->get_tooltip($geo)];
        }, $this->geos);
        $dark_mode = \IAWP_SCOPED\iawp()->get_option('iawp_dark_mode', '0');
        \ob_start();
        ?>
        <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
        <div class="chart-container">
            <div class="chart-inner">
                <div class="legend-container">
                    <h2 class="legend-title"><?php 
        echo $this->title;
        ?></h2>
                </div>
                <div id="myChart"
                     data-controller="chart-geo"
                     data-chart-geo-data-value="<?php 
        \esc_attr_e(\json_encode($chart_data));
        ?>"
                     data-chart-geo-dark-mode-value="<?php 
        \esc_attr_e($dark_mode);
        ?>">
                    <div data-chart-geo-target="chart"></div>
                </div>
            </div>
        </div><?php 
        $html = \ob_get_contents();
        \ob_end_clean();
        return $html;
    }
}
