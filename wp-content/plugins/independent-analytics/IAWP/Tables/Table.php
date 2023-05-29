<?php

namespace IAWP_SCOPED\IAWP\Tables;

use IAWP_SCOPED\IAWP\Dashboard_Options;
use IAWP_SCOPED\IAWP\Date_Range\Relative_Date_Range;
use IAWP_SCOPED\IAWP\Filters;
use IAWP_SCOPED\IAWP\Statistics\Statistics;
use IAWP_SCOPED\IAWP\Utils\Array_To_CSV;
use IAWP_SCOPED\IAWP\Utils\Currency;
use IAWP_SCOPED\IAWP\Utils\Timezone;
use IAWP_SCOPED\IAWP\Utils\WordPress_Site_Date_Format_Pattern;
use IAWP_SCOPED\IAWP\Utils\Number_Formatter;
use IAWP_SCOPED\IAWP\Utils\Security;
abstract class Table
{
    /**
     * @return array<Column>
     */
    protected abstract function local_columns() : array;
    protected abstract function table_name() : string;
    private $filters;
    private $visible_columns;
    private $statistics;
    public function __construct($visible_columns = null)
    {
        $this->filters = new Filters();
        $this->visible_columns = $visible_columns;
    }
    public function get_columns() : array
    {
        if ($this->visible_columns === null) {
            return $this->local_columns();
        }
        return \array_map(function ($column) {
            if (\in_array($column->id(), $this->visible_columns)) {
                $column->set_visible(\true);
            } else {
                $column->set_visible(\false);
            }
            return $column;
        }, $this->local_columns());
    }
    public function visible_column_ids()
    {
        $visible_columns = [];
        foreach ($this->get_columns() as $column) {
            if ($column->visible()) {
                $visible_columns[] = $column->id();
            }
        }
        return $visible_columns;
    }
    public function get_table_markup()
    {
        $opts = new Dashboard_Options();
        $templates = \IAWP_SCOPED\iawp()->templates();
        return $templates->render('table/index', ['just_rows' => \false, 'table_name' => $this->table_name(), 'all_columns' => $this->get_columns(), 'visible_column_count' => $this->visible_column_count(), 'row_count' => 0, 'rows' => [], 'render_skeleton' => \true, 'page_size' => \IAWP_SCOPED\iawp()->pagination_page_size(), 'opts' => $opts]);
    }
    public function set_statistics(Statistics $statistics)
    {
        $this->statistics = $statistics;
    }
    public function get_row_markup($rows)
    {
        return $this->get_rendered_template($rows, \true);
    }
    /**
     * Get the number of visible columns
     *
     * @return int
     */
    private function visible_column_count() : int
    {
        $visible_columns = 0;
        foreach ($this->get_columns() as $column) {
            if ($column->visible()) {
                $visible_columns++;
            }
        }
        return $visible_columns;
    }
    private function get_rendered_template($rows, $just_rows = \false)
    {
        $opts = new Dashboard_Options();
        $templates = \IAWP_SCOPED\iawp()->templates();
        $templates->registerFunction('row_data_attributes', [$this, 'get_row_data_attributes']);
        $templates->registerFunction('cell_content', [$this, 'get_cell_content']);
        return $templates->render('table/index', ['just_rows' => $just_rows, 'table_name' => $this->table_name(), 'all_columns' => $this->get_columns(), 'visible_column_count' => $this->visible_column_count(), 'row_count' => \count($rows), 'rows' => $rows, 'render_skeleton' => \false, 'page_size' => \IAWP_SCOPED\iawp()->pagination_page_size(), 'opts' => $opts]);
    }
    public function get_row_data_attributes($row)
    {
        $html = '';
        foreach ($this->get_columns() as $column) {
            $id = $column->id();
            $data_val = $row->{$id}();
            $html .= ' data-' . \esc_attr($column->id()) . '="' . \esc_attr($data_val) . '"';
        }
        return $html;
    }
    public function get_cell_content($row, $column_id)
    {
        if (\is_null($row->{$column_id}())) {
            return '-';
        }
        if ($column_id == 'title' && $row->is_deleted()) {
            return Security::string($row->{$column_id}()) . ' <span class="deleted-label">' . \esc_html__('(deleted)', 'iawp') . '</span>';
        } elseif ($column_id == 'views') {
            $views = \number_format($row->views(), 0);
            // Getting a divide by zero error from the line below?
            // It's likely an issue with $this->views which is an instance of Views. Make sure the queries there are working.
            $views_percentage = Number_Formatter::format($row->views() / $this->statistics->views() * 100, 'percent', 2);
            return Security::string($views) . ' <span class="percentage">(' . Security::string($views_percentage) . ')</span>';
        } elseif ($column_id == 'visitors') {
            $visitors = \number_format($row->visitors(), 0);
            $visitors_percentage = Number_Formatter::format($row->visitors() / $this->statistics->visitors() * 100, 'percent', 2);
            return Security::string($visitors) . ' <span class="percentage">(' . Security::string($visitors_percentage) . ')</span>';
        } elseif ($column_id == 'sessions') {
            $sessions = \number_format($row->sessions(), 0);
            $sessions_percentage = Number_Formatter::format($row->sessions() / $this->statistics->sessions() * 100, 'percent', 2);
            return Security::string($sessions) . ' <span class="percentage">(' . Security::string($sessions_percentage) . ')</span>';
        } elseif ($column_id === 'average_session_duration' || $column_id === 'average_view_duration') {
            return Number_Formatter::second_to_minute_timestamp($row->{$column_id}());
        } elseif ($column_id === 'views_growth' || $column_id === 'visitors_growth' || $column_id === 'woocommerce_conversion_rate') {
            return Number_Formatter::format($row->{$column_id}(), 'percent', 2);
        } elseif ($column_id == 'url') {
            if ($row->is_deleted()) {
                return Security::string(\esc_url($row->url()));
            } else {
                return '<a href="' . Security::string(\esc_url($row->url(\true))) . '" target="_blank" class="external-link">' . Security::string(\esc_url($row->url())) . '<span class="dashicons dashicons-external"></span></a>';
            }
        } elseif ($column_id == 'author') {
            return Security::html($row->avatar()) . ' ' . Security::string($row->author());
        } elseif ($column_id == 'date') {
            return Security::string(\date(WordPress_Site_Date_Format_Pattern::for_php(), $row->date()));
        } elseif ($column_id == 'type' && \method_exists($row, 'icon') && \method_exists($row, 'type')) {
            return $row->icon(0) . ' ' . Security::string($row->type());
        } elseif ($column_id == 'referrer' && !$row->is_direct()) {
            return '<a href="' . \esc_url($row->referrer_url()) . '" target="_blank" class="external-link">' . Security::string($row->referrer()) . '<span class="dashicons dashicons-external"></span></a>';
        } elseif ($column_id === 'country') {
            return '<img class="flag" alt="Country flag" src="' . Security::string($row->flag()) . '"/>' . Security::string($row->country());
        } elseif ($column_id === 'wc_gross_sales' || $column_id === 'wc_refunded_amount' || $column_id === 'wc_net_sales' || $column_id === 'woocommerce_earnings_per_visitor' || $column_id === 'woocommerce_average_order_volume') {
            return Security::string(Currency::format($row->{$column_id}()));
        } else {
            return Security::string($row->{$column_id}());
        }
    }
    private function filters()
    {
        return $this->filters;
    }
    public function output_toolbar()
    {
        $opts = new Dashboard_Options();
        ?>
        <div class="toolbar">
        <div class="buttons">
            <div class="modal-parent"
                 data-controller="dates"
                 data-dates-relative-range-id-value="<?php 
        \esc_html_e($opts->relative_range_id());
        ?>"
                 data-dates-exact-start-value="<?php 
        \esc_html_e($opts->get_date_range()->start()->setTimezone(Timezone::wordpress_site_timezone())->format('Y-m-d'));
        ?>"
                 data-dates-exact-end-value="<?php 
        \esc_html_e($opts->get_date_range()->end()->setTimezone(Timezone::wordpress_site_timezone())->format('Y-m-d'));
        ?>"
                 data-dates-first-day-of-week-value="<?php 
        echo \absint(\IAWP_SCOPED\iawp()->get_option('iawp_dow', 0));
        ?>"
                 data-dates-css-url-value="<?php 
        echo \esc_url(\IAWP_SCOPED\iawp_url_to('dist/styles/easepick/datepicker.css'));
        ?>"
                 data-dates-format-value="<?php 
        echo \esc_attr(WordPress_Site_Date_Format_Pattern::for_javascript());
        ?>"
            >
                <button id="dates-button"
                        class="iawp-button ghost-white toolbar-button"
                        data-action="dates#toggleModal"
                        data-dates-target="modalButton"
                >
                    <span class="dashicons dashicons-calendar-alt"></span>
                    <span class="iawp-label"><?php 
        \esc_html_e($opts->get_date_range()->label());
        ?></span>
                </button>
                <div id="modal-dates"
                     class="modal large flex"
                     data-dates-target="modal"
                >
                    <div class="modal-inner">
                        <div id="easepick-picker"
                             data-dates-target="easepick"
                             style="display: none;"
                        >
                        </div>
                        <div class="relative-dates">
                            <?php 
        foreach (Relative_Date_Range::ranges() as $date_range) {
            ?>
                                <button class="iawp-button ghost-purple"
                                        data-dates-target="relativeRange"
                                        data-action="dates#relativeRangeSelected"
                                        data-relative-range-id="<?php 
            \esc_html_e($date_range->relative_range_id());
            ?>"
                                        data-relative-range-label="<?php 
            \esc_html_e($date_range->label());
            ?>"
                                        data-relative-range-start="<?php 
            \esc_html_e($date_range->start()->setTimezone(Timezone::wordpress_site_timezone())->format('Y-m-d'));
            ?>"
                                        data-relative-range-end="<?php 
            \esc_html_e($date_range->end()->setTimezone(Timezone::wordpress_site_timezone())->format('Y-m-d'));
            ?>"
                                >
                                    <?php 
            \esc_html_e($date_range->label());
            ?>
                                </button>
                            <?php 
        }
        ?>
                        </div>
                        <div>
                            <hr/>
                        </div>
                        <div>
                            <button class="iawp-button purple"
                                    data-dates-target="apply"
                                    data-action="dates#apply"
                            >
                                <?php 
        \esc_html_e('Apply', 'iawp');
        ?>
                            </button>
                            <button class="iawp-button ghost-purple"
                                    data-action="dates#closeModal"
                            >
                                <?php 
        \esc_html_e('Cancel', 'iawp');
        ?>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <?php 
        $this->filters()->output_filters($this->get_columns());
        ?>
            <?php 
        $this->column_picker();
        ?>
        </div>
        <div class="buttons">
            <button class="iawp-button ghost-white toolbar-button"
                    data-controller="clipboard"
                    data-action="clipboard#copy"
            >
                <span class="dashicons dashicons-admin-page"></span>
                <span data-clipboard-target="statusTextElement" class="iawp-label">
                    <?php 
        \esc_html_e('Copy Dashboard URL', 'iawp');
        ?>
                </span>
            </button>
            <a class="learn-more"
               href="https://independentwp.com/knowledgebase/dashboard/save-reports-revisit-later/"
               target="_blank"><span class="dashicons dashicons-info-outline"></span></a>
        </div>
        </div><?php 
    }
    private function column_picker()
    {
        ?>

        <div class="customize-columns modal-parent small"
             data-controller="columns"
        >
            <button id="customize-columns"
                    class="iawp-button ghost-white toolbar-button"
                    data-action="columns#toggleModal"
                    data-columns-target="modalButton"
            >
                <span class="dashicons dashicons-columns"></span>
                <span class="iawp-label"><?php 
        \esc_html_e('Edit Columns', 'iawp');
        ?></span>
            </button>
            <div id="modal-columns"
                 class="modal small"
                 data-columns-target="modal"
            >
                <div class="modal-inner">
                    <div class="title-small">
                        <?php 
        \esc_html_e('Columns', 'iawp');
        ?>
                    </div>
                    <?php 
        foreach ($this->get_columns() as $column) {
            ?>
                        <?php 
            if ($column->id() === 'wc_orders') {
                ?>
                            <p class="title-small wc-title">
                                <?php 
                \esc_html_e('WooCommerce', 'iawp');
                ?>
                                <?php 
                if (\IAWP_SCOPED\iawp_is_free()) {
                    ?>
                                    <span class="pro-label"><?php 
                    \esc_html_e('PRO', 'iawp');
                    ?></span>
                                <?php 
                }
                ?>
                            </p>
                        <?php 
            }
            ?>

                        <label class="column-label"
                               for="iawp-column-<?php 
            echo \esc_attr($column->id());
            ?>"
                        >
                            <input id="iawp-column-<?php 
            echo \esc_attr($column->id());
            ?>"
                                <?php 
            if ($column->requires_woocommerce() && (\IAWP_SCOPED\iawp_is_free() || !\IAWP_SCOPED\iawp_using_woocommerce())) {
                ?>
                                    <?php 
                \checked(\true, \false, \true);
                ?>
                                    disabled="disabled"
                                    data-locked-behind-pro="true"
                                <?php 
            } else {
                ?>
                                    <?php 
                \checked(\true, $column->visible(), \true);
                ?>
                                    data-columns-target="checkbox"
                                    data-action="columns#check"
                                <?php 
            }
            ?>
                                   type="checkbox"
                                   name="<?php 
            \esc_attr_e($column->id());
            ?>"
                                   data-test-visibility="<?php 
            echo $column->visible() ? 'visible' : 'hidden';
            ?>"
                            >
                            <span><?php 
            echo \esc_html($column->label());
            ?></span>
                        </label>
                    <?php 
        }
        ?>
                </div>
            </div>
        </div>
        <?php 
    }
    public final function csv($rows)
    {
        $columns = $this->get_columns();
        $csv_header = [];
        $csv_rows = [];
        foreach ($columns as $column) {
            if (!$column->exportable() || $column->requires_woocommerce() && \IAWP_SCOPED\iawp_is_free()) {
                continue;
            }
            $csv_header[] = $column->label();
        }
        foreach ($rows as $row) {
            $csv_row = [];
            foreach ($columns as $column) {
                if (!$column->exportable() || $column->requires_woocommerce() && \IAWP_SCOPED\iawp_is_free()) {
                    continue;
                }
                $column_id = $column->id();
                $value = $row->{$column_id}();
                // Todo - This logic is similar to the rendering logic for table cells. This should
                //  all be handled via the column class itself.
                if (\is_null($value)) {
                    $csv_row[] = '-';
                } elseif ($column_id === 'date') {
                    $csv_row[] = \date(WordPress_Site_Date_Format_Pattern::for_php(), $value);
                } elseif ($column_id === 'average_session_duration' || $column_id === 'average_view_duration') {
                    $csv_row[] = Number_Formatter::second_to_minute_timestamp($value);
                } else {
                    $csv_row[] = $row->{$column_id}();
                }
            }
            $csv_rows[] = $csv_row;
        }
        $csv = \array_merge([$csv_header], $csv_rows);
        return Array_To_CSV::array2csv($csv);
    }
}
