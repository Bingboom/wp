<?php

namespace IAWP_SCOPED\IAWP\Tables;

class Table_Pages extends Table
{
    protected function local_columns() : array
    {
        return [new Column(['id' => 'title', 'label' => \esc_html__('Title', 'iawp'), 'visible' => \true, 'sort_direction' => 'asc']), new Column(['id' => 'visitors', 'label' => \esc_html__('Visitors', 'iawp'), 'visible' => \true, 'sort_direction' => 'desc']), new Column(['id' => 'views', 'label' => \esc_html__('Views', 'iawp'), 'visible' => \true, 'sort_direction' => 'desc']), new Column(['id' => 'sessions', 'label' => \esc_html__('Sessions', 'iawp'), 'visible' => \true, 'sort_direction' => 'desc']), new Column(['id' => 'average_view_duration', 'label' => \esc_html__('View Duration', 'iawp'), 'visible' => \true, 'sort_direction' => 'desc']), new Column(['id' => 'visitors_growth', 'label' => \esc_html__('Visitors Growth', 'iawp'), 'visible' => \false, 'sort_direction' => 'desc', 'exportable' => \false]), new Column(['id' => 'views_growth', 'label' => \esc_html__('Views Growth', 'iawp'), 'visible' => \false, 'sort_direction' => 'desc', 'exportable' => \false]), new Column(['id' => 'url', 'label' => \esc_html__('URL', 'iawp'), 'visible' => \true, 'sort_direction' => 'asc']), new Column(['id' => 'author', 'label' => \esc_html__('Author', 'iawp'), 'visible' => \false, 'sort_direction' => 'asc']), new Column(['id' => 'type', 'label' => \esc_html__('Page Type', 'iawp'), 'visible' => \true, 'sort_direction' => 'asc']), new Column(['id' => 'date', 'label' => \esc_html__('Publish Date', 'iawp'), 'visible' => \false, 'sort_direction' => 'asc']), new Column(['id' => 'category', 'label' => \esc_html__('Post Category', 'iawp'), 'visible' => \false, 'sort_direction' => 'asc']), new Column(['id' => 'comments', 'label' => \esc_html__('Comments', 'iawp'), 'visible' => \false, 'sort_direction' => 'desc'])];
    }
    protected function table_name() : string
    {
        return 'views';
    }
}
