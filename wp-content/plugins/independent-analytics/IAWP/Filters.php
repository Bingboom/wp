<?php

namespace IAWP_SCOPED\IAWP;

use IAWP_SCOPED\IAWP\Utils\WordPress_Site_Date_Format_Pattern;
use IAWP_SCOPED\IAWP\Utils\Security;
class Filters
{
    public function output_filters(array $columns)
    {
        $opts = new Dashboard_Options();
        ?>
    <div class="modal-parent"
         data-controller="filters"
         data-filters-filters-value="<?php 
        \esc_html_e(\json_encode($opts->filters()));
        ?>"
    >
        <button id="filters-button" class="iawp-button ghost-white toolbar-button"
                data-action="filters#toggleModal"
                data-filters-target="modalButton"
        >
            <span class="dashicons dashicons-filter"></span>
            <span class="iawp-label"><?php 
        echo ' ' . \esc_html__('Filter Rows', 'iawp') . ' ';
        ?></span>
            <span class="count" data-filters-target="filterCount"></span>
        </button>
        <div class="modal large"
             data-filters-target="modal"
        >
            <div class="modal-inner">
                <div class="title-small"><?php 
        \esc_html_e('Filters', 'iawp');
        ?></div>
                <div id="filters" data-filters-target="filters" class="filters" data-filters="[]">
                </div>
                <template data-filters-target="blueprint">
                    <?php 
        self::get_condition_html($columns);
        ?>
                </template>
                <div>
                    <button id="add-condition" class="iawp-button text"
                            data-action="filters#addCondition"
                    >
                        <?php 
        \esc_html_e('+ Add another condition', 'iawp');
        ?>
                    </button>
                </div>
                <div class="actions">
                    <button id="filters-apply" class="iawp-button purple"
                            data-action="filters#apply"
                    >
                        <?php 
        \esc_html_e('Apply', 'iawp');
        ?>
                    </button>
                    <button id="filters-reset" class="iawp-button ghost-purple"
                            data-action="filters#reset"
                            data-filters-target="reset"
                            disabled
                    >
                        <?php 
        \esc_html_e('Reset', 'iawp');
        ?>
                    </button>
                </div>
            </div>
        </div>
        </div><?php 
    }
    private function get_condition_html(array $columns)
    {
        ?>
        <div class="condition" data-filters-target="condition">
            <div class="input-group">
                <div>
                    <?php 
        echo self::get_inclusion_selects();
        ?>
                </div>
                <div>
                    <?php 
        echo self::get_column_select($columns);
        ?>
                </div>
                <div class="operator-select-container">
                    <?php 
        echo self::get_all_operator_selects();
        ?>
                </div>
                <div class="operand-field-container">
                    <?php 
        echo self::get_all_operand_fields($columns);
        ?>
                </div>
            </div>
            <button class="delete-button" data-action="filters#removeCondition">
                <span class="dashicons dashicons-no"></span></button>
        </div>
        <?php 
    }
    private function get_inclusion_selects()
    {
        $html = '<select class="filters-include" data-filters-target="inclusion">';
        $html .= '<option value="include">' . \esc_html__('Include', 'iawp') . '</option>';
        $html .= '<option value="exclude">' . \esc_html__('Exclude', 'iawp') . '</option>';
        $html .= '</select>';
        return $html;
    }
    private function get_column_select(array $columns)
    {
        ?>
        <select class="filters-column" data-filters-target="column"
                data-action="filters#columnSelect"
        >
            <option value="">
                <?php 
        \esc_html_e('Choose a column', 'iawp');
        ?>
            </option>
            <?php 
        foreach ($columns as $column) {
            ?>
                <?php 
            if ($column->requires_woocommerce() && !\IAWP_SCOPED\iawp_using_woocommerce()) {
                continue;
            }
            ?>
                <option value="<?php 
            \esc_attr_e($column->id());
            ?>"
                        data-type="<?php 
            \esc_attr_e(self::get_column_data_type($column->id()));
            ?>"
                >
                    <?php 
            \esc_html_e($column->label());
            ?>
                </option>
            <?php 
        }
        ?>
        </select>
        <?php 
    }
    private function get_all_operator_selects()
    {
        $html = '';
        foreach (self::get_data_types() as $data_type) {
            $html .= '<select data-filters-target="operator" data-type="' . \esc_attr($data_type) . '">';
            foreach (self::get_operators($data_type) as $key => $value) {
                $html .= '<option value="' . \esc_attr($key) . '">' . \esc_html($value) . '</option>';
            }
            $html .= '</select>';
        }
        return $html;
    }
    private function get_all_operand_fields($columns)
    {
        $html = '';
        foreach ($columns as $column) {
            $id = $column->id();
            if ($id == 'title') {
                $html .= '<input data-filters-target="operand" data-action="keydown->filters#operandKeyDown filters#operandChange" data-column="title" type="text" />';
            } elseif ($id == 'referrer') {
                $html .= '<input data-filters-target="operand" data-action="keydown->filters#operandKeyDown filters#operandChange" data-column="referrer" type="text" />';
            } elseif ($id == 'continent') {
                $html .= '<input data-filters-target="operand" data-action="keydown->filters#operandKeyDown filters#operandChange" data-column="continent" type="text" />';
            } elseif ($id == 'country') {
                $html .= '<input data-filters-target="operand" data-action="keydown->filters#operandKeyDown filters#operandChange" data-column="country" type="text" />';
            } elseif ($id == 'subdivision') {
                $html .= '<input data-filters-target="operand" data-action="keydown->filters#operandKeyDown filters#operandChange" data-column="subdivision" type="text" />';
            } elseif ($id == 'city') {
                $html .= '<input data-filters-target="operand" data-action="keydown->filters#operandKeyDown filters#operandChange" data-column="city" type="text" />';
            } elseif ($id == 'utm_source') {
                $html .= '<input data-filters-target="operand" data-action="keydown->filters#operandKeyDown filters#operandChange" data-column="utm_source" type="text" />';
            } elseif ($id == 'utm_medium') {
                $html .= '<input data-filters-target="operand" data-action="keydown->filters#operandKeyDown filters#operandChange" data-column="utm_medium" type="text" />';
            } elseif ($id == 'utm_campaign') {
                $html .= '<input data-filters-target="operand" data-action="keydown->filters#operandKeyDown filters#operandChange" data-column="utm_campaign" type="text" />';
            } elseif ($id == 'utm_term') {
                $html .= '<input data-filters-target="operand" data-action="keydown->filters#operandKeyDown filters#operandChange" data-column="utm_term" type="text" />';
            } elseif ($id == 'utm_content') {
                $html .= '<input data-filters-target="operand" data-action="keydown->filters#operandKeyDown filters#operandChange" data-column="utm_content" type="text" />';
            } elseif ($id == 'referrer_type') {
                $html .= '<select data-filters-target="operand" data-column="referrer_type">';
                $html .= '<option value="Search">' . \esc_html__('Search', 'iawp') . '</option>';
                $html .= '<option value="Social">' . \esc_html__('Social', 'iawp') . '</option>';
                $html .= '<option value="Referrer">' . \esc_html__('Referrer', 'iawp') . '</option>';
                $html .= '<option value="Direct">' . \esc_html__('Direct', 'iawp') . '</option>';
                $html .= '</select>';
            } elseif ($id == 'url') {
                $html .= '<input type="text" data-filters-target="operand" data-action="keydown->filters#operandKeyDown filters#operandChange" data-column="url"/>';
            } elseif ($id == 'views') {
                $html .= '<input type="number" data-filters-target="operand" data-action="keydown->filters#operandKeyDown filters#operandChange" data-column="views"/>';
            } elseif ($id == 'views_growth') {
                $html .= '<input type="number" data-filters-target="operand" data-action="keydown->filters#operandKeyDown filters#operandChange" data-column="views_growth"/>';
            } elseif ($id == 'visitors') {
                $html .= '<input type="number" data-filters-target="operand" data-action="keydown->filters#operandKeyDown filters#operandChange" data-column="visitors"/>';
            } elseif ($id == 'visitors_growth') {
                $html .= '<input type="number" data-filters-target="operand" data-action="keydown->filters#operandKeyDown filters#operandChange" data-column="visitors_growth"/>';
            } elseif ($id == 'sessions') {
                $html .= '<input type="number" data-filters-target="operand" data-action="keydown->filters#operandKeyDown filters#operandChange" data-column="sessions"/>';
            } elseif ($id == 'average_session_duration') {
                $html .= '<input type="number" data-filters-target="operand" data-action="keydown->filters#operandKeyDown filters#operandChange" data-column="average_session_duration"/>';
            } elseif ($id == 'average_view_duration') {
                $html .= '<input type="number" data-filters-target="operand" data-action="keydown->filters#operandKeyDown filters#operandChange" data-column="average_view_duration"/>';
            } elseif ($id == 'comments') {
                $html .= '<input type="number" data-filters-target="operand" data-action="keydown->filters#operandKeyDown filters#operandChange" data-column="comments"/>';
            } elseif (\in_array($id, ['wc_orders', 'wc_gross_sales', 'wc_refunds', 'wc_refunded_amount', 'wc_net_sales', 'woocommerce_conversion_rate', 'woocommerce_earnings_per_visitor', 'woocommerce_average_order_volume'])) {
                $html .= '<input type="number" data-filters-target="operand" data-action="keydown->filters#operandKeyDown filters#operandChange" data-column="' . \esc_attr($id) . '"/>';
            } elseif ($id == 'author') {
                $html .= '<select data-filters-target="operand" data-action="keydown->filters#operandKeyDown filters#operandChange" data-column="author">';
                foreach (\IAWP_SCOPED\iawp()->get_users_can_write() as $author) {
                    $html .= '<option value="' . \esc_attr($author->ID) . '">' . \esc_html($author->display_name) . '</option>';
                }
                $html .= '</select>';
            } elseif ($id == 'type') {
                $html .= '<select data-filters-target="operand" data-action="keydown->filters#operandKeyDown filters#operandChange" data-column="type">';
                $html .= '<option value="post">' . \esc_html__('Post', 'iawp') . '</option>';
                $html .= '<option value="page">' . \esc_html__('Page', 'iawp') . '</option>';
                $html .= '<option value="attachment">' . \esc_html__('Attachment', 'iawp') . '</option>';
                foreach (\IAWP_SCOPED\iawp()->get_custom_types() as $cpt) {
                    $html .= '<option value="' . \esc_attr($cpt) . '">' . \esc_html(\get_post_type_object($cpt)->labels->singular_name) . '</option>';
                }
                $html .= '<option value="category">' . \esc_html__('Category', 'iawp') . '</option>';
                $html .= '<option value="post_tag">' . \esc_html__('Tag', 'iawp') . '</option>';
                foreach (\IAWP_SCOPED\iawp()->get_custom_types(\true) as $tax) {
                    $html .= '<option value="' . \esc_attr($tax) . '">' . \esc_html(\get_taxonomy_labels(\get_taxonomy($tax))->singular_name) . '</option>';
                }
                $html .= '<option value="blog-archive">' . \esc_html__('Blog Home', 'iawp') . '</option>';
                $html .= '<option value="author-archive">' . \esc_html__('Author Archive', 'iawp') . '</option>';
                $html .= '<option value="date-archive">' . \esc_html__('Date Archive', 'iawp') . '</option>';
                $html .= '<option value="search-archive">' . \esc_html__('Search Results', 'iawp') . '</option>';
                $html .= '<option value="not-found">' . \esc_html__('404', 'iawp') . '</option>';
                $html .= '</select>';
            } elseif ($id == 'date') {
                $html .= '<input type="text" 
                        data-filters-target="operand"
                        data-action="keydown->filters#operandKeyDown filters#operandChange"
                        data-column="date"
                        data-controller="easepick"
                        data-css="' . \esc_url(\IAWP_SCOPED\iawp_url_to('dist/styles/easepick/datepicker.css')) . '" data-dow="' . \absint(\IAWP_SCOPED\iawp()->get_option('iawp_dow', 1)) . '" 
                        data-format="' . \esc_attr(WordPress_Site_Date_Format_Pattern::for_javascript()) . '" />';
            } elseif ($id == 'category') {
                $html .= '<select data-filters-target="operand" data-action="keydown->filters#operandKeyDown filters#operandChange" data-column="category">';
                foreach (\get_categories() as $category) {
                    $html .= '<option value="' . \esc_attr($category->term_id) . '">' . \esc_html($category->name) . '</option>';
                }
                $html .= '</select>';
            }
        }
        return $html;
    }
    private function get_data_types()
    {
        return ['string', 'int', 'date', 'bool'];
    }
    private function get_operators(string $data_type)
    {
        if ($data_type == 'string') {
            return ['contains' => \esc_html__('Contains', 'iawp'), 'exact' => \esc_html__('Exactly matches', 'iawp')];
        } elseif ($data_type == 'int') {
            return ['greater' => \esc_html__('Greater than', 'iawp'), 'lesser' => \esc_html__('Less than', 'iawp'), 'equal' => \esc_html__('Equal to', 'iawp')];
        } elseif ($data_type == 'bool') {
            return ['is' => \esc_html__('Is', 'iawp'), 'isnt' => \esc_html__('Isn\'t', 'iawp')];
        } elseif ($data_type == 'date') {
            return ['before' => \esc_html__('Before', 'iawp'), 'after' => \esc_html__('After', 'iawp'), 'on' => \esc_html__('On', 'iawp')];
        } else {
            return null;
        }
    }
    private function get_column_data_type(string $column)
    {
        $columns = ['title' => 'string', 'url' => 'string', 'referrer' => 'string', 'continent' => 'string', 'country' => 'string', 'subdivision' => 'string', 'city' => 'string', 'utm_source' => 'string', 'utm_medium' => 'string', 'utm_campaign' => 'string', 'utm_term' => 'string', 'utm_content' => 'string', 'author' => 'bool', 'type' => 'bool', 'referrer_type' => 'bool', 'category' => 'bool', 'views' => 'int', 'views_growth' => 'int', 'visitors' => 'int', 'visitors_growth' => 'int', 'sessions' => 'int', 'average_session_duration' => 'int', 'average_view_duration' => 'int', 'comments' => 'int', 'date' => 'date', 'wc_orders' => 'int', 'wc_gross_sales' => 'int', 'wc_refunds' => 'int', 'wc_refunded_amount' => 'int', 'wc_net_sales' => 'int', 'woocommerce_conversion_rate' => 'int', 'woocommerce_earnings_per_visitor' => 'int', 'woocommerce_average_order_volume' => 'int'];
        if (\array_key_exists($column, $columns)) {
            return $columns[$column];
        } else {
            return null;
        }
    }
    public function fitler_rows(array $rows, array $filters)
    {
        if ($filters == '') {
            return $rows;
        }
        $filtered_rows = [];
        foreach ($rows as $row) {
            $match_count = 0;
            foreach ($filters as $filter) {
                if (self::include_row($row, $filter)) {
                    $match_count++;
                }
            }
            if ($match_count == \count($filters)) {
                $filtered_rows[] = $row;
            }
        }
        return $filtered_rows;
    }
    public function include_row($row, $filter)
    {
        $inclusion = $filter['inclusion'];
        $column = $filter['column'];
        $operator = $filter['operator'];
        $operand = \strtolower($filter['operand']);
        $column_data_type = $this->get_column_data_type($column);
        $match = \false;
        if ($column_data_type === 'int') {
            if ($operator === 'greater') {
                if ($row->{$column}() > $operand) {
                    $match = \true;
                }
            } elseif ($operator === 'lesser') {
                if ($row->{$column}() < $operand && !\is_null($row->{$column}())) {
                    $match = \true;
                }
            } elseif ($operator === 'equal') {
                if ($row->{$column}() == $operand) {
                    $match = \true;
                }
            }
        } elseif ($column_data_type === 'string') {
            $value = \strtolower($row->{$column}());
            if ($operator == 'contains') {
                if (\strpos($value, $operand) !== \false) {
                    $match = \true;
                }
            } elseif ($operator == 'exact') {
                if ($value == $operand) {
                    $match = \true;
                }
            }
        } elseif ($filter['column'] == 'referrer_type') {
            $referrer_type = \strtolower($row->referrer_type());
            if ($filter['operator'] == 'is' && $referrer_type == $operand) {
                $match = \true;
            } elseif ($filter['operator'] == 'isnt' && $referrer_type != $operand) {
                $match = \true;
            }
        } elseif ($filter['column'] == 'author') {
            if ($filter['operator'] == 'is' && $row->author_id() == $operand) {
                $match = \true;
            } elseif ($filter['operator'] == 'isnt' && $row->author_id() != $operand) {
                $match = \true;
            }
        } elseif ($filter['column'] == 'type') {
            if ($filter['operator'] == 'is' && $row->type(\true) == $operand) {
                $match = \true;
            } elseif ($filter['operator'] == 'isnt' && $row->type(\true) != $operand) {
                $match = \true;
            }
        } elseif ($filter['column'] == 'date') {
            // Don't "continue" or row won't be included when excluding
            if ($row->date() != null) {
                $date = new \DateTime($operand);
                if ($filter['operator'] == 'before' && $row->date() < $date->format('U')) {
                    $match = \true;
                } elseif ($filter['operator'] == 'after' && $row->date() > $date->format('U')) {
                    $match = \true;
                } elseif ($filter['operator'] == 'on') {
                    $formatted_date = \DateTime::createFromFormat('U', $row->date());
                    $formatted_date = $formatted_date->format('Y-m-d');
                    if ($date->format('Y-m-d') == $formatted_date) {
                        $match = \true;
                    }
                }
            }
        } elseif ($filter['column'] == 'category') {
            if ($filter['operator'] == 'is' && \in_array($operand, $row->category(\false) ?? [])) {
                $match = \true;
            } elseif ($filter['operator'] == 'isnt' && !\in_array($operand, $row->category(\false) ?? [])) {
                $match = \true;
            }
        }
        if ($filter['inclusion'] == 'include' && $match || $filter['inclusion'] == 'exclude' && !$match) {
            return \true;
        } else {
            return \false;
        }
    }
}
