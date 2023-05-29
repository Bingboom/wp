<?php $this->start('rows') ?>
<div id="iawp-rows" class="iawp-rows">
    <?php if ($row_count == 0): ?>
        <!-- No rows -->
        <?php if ($table_name == 'views'): ?>
            <p id="data-error"
               class="data-error"><?php esc_html_e('No views found', 'iawp'); ?></p>
        <?php elseif ($table_name == 'referrers'): ?>
            <p id="data-error"
               class="data-error"><?php esc_html_e('No referrers found', 'iawp'); ?></p>
        <?php elseif ($table_name == 'geo'): ?>
            <p id="data-error"
               class="data-error"><?php esc_html_e('No geographic data found', 'iawp'); ?></p>
        <?php elseif ($table_name == 'campaigns'): ?>
            <div class="data-error">

                <p>
                    <?php esc_html_e('No campaign data found', 'iawp'); ?>
                </p>
                <p>
                    <a href="?page=independent-analytics&tab=campaign-builder"
                       class="iawp-button purple">
                        <?php esc_html_e('Create your first campaign', 'iawp'); ?>
                    </a>
                </p>
            </div>
        <?php endif; ?>

    <?php else: ?>
        <!-- Some rows -->
        <?php foreach ($rows as $index => $row): ?>
            <?php $class = $table_name == 'views' && $row->is_deleted() ? 'iawp-row deleted' : 'iawp-row'; ?>
            <div class="<?php echo esc_attr($class); ?>" <?php echo $this->row_data_attributes($row); ?>>
                <?php foreach ($all_columns as $column): ?>
                    <?php $class = $column->visible() ? 'cell' : 'cell hide'; ?>
                    <div class="<?php echo esc_attr($class); ?>"
                         data-column="<?php echo esc_attr($column->id()); ?>"
                         data-test-visibility="<?php echo $column->visible() ? 'visible' : 'hidden'; ?>"
                    >
                        <div class="row-number"><?php echo $index + 1; ?></div>
                        <span class="cell-content"><?php echo $this->cell_content($row, $column->id()); ?></span>
                        <span class="animator"></span>
                    </div>
                <?php endforeach ?>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
<?php $this->stop() ?>

<?php $this->start('table') ?>
<div class="data-table-container">
<div id='data-table'
     class='data-table'
     data-table-name='<?php echo esc_attr($table_name); ?>'
     data-columns='<?php echo \IAWP\Utils\Security::json_encode($all_columns) ?>'
     data-column-count='<?php echo count($all_columns); ?>'
     style="margin: 2px; --columns: <?php echo absint($visible_column_count); ?>; --columns-mobile: <?php echo absint($visible_column_count - 1); ?>"
>

    <!-- Header -->
    <div id="columns" class="columns">
        <div class="iawp-row"
             data-controller="sort"
        >
            <?php foreach ($all_columns as $column): ?>
                <?php $cell_class = $column->visible() ? 'cell' : 'cell hide'; ?>
                <div class="<?php echo esc_attr($cell_class); ?>"
                     data-column="<?php echo esc_attr($column->id()); ?>"
                     data-test-visibility="<?php echo $column->visible() ? 'visible' : 'hidden'; ?>"
                >
                    <button class="sort-button"
                            data-sort-target="sortButton"
                            data-sort-direction="<?php echo $column->id() === $opts->sort_by() ? $opts->sort_direction() : '' ?>"
                            data-default-sort-direction="<?php echo $column->sort_direction() ?>"
                            data-sort-column="<?php echo esc_attr($column->id()); ?>"
                            data-action="sort#sortByColumn"
                    >
                        <div class="row-number"></div>
                        <span class="name"><?php echo esc_html($column->label()); ?></span>
                        <span class="dashicons dashicons-arrow-right"></span>
                        <span class="dashicons dashicons-arrow-up"></span>
                        <span class="dashicons dashicons-arrow-down"></span>
                        <div class="animator"></div>
                    </button>
                </div>
            <?php endforeach ?>
        </div>
    </div>

    <!-- Rows -->
    <?php if ($render_skeleton) : ?>
        <div id="iawp-rows" class="iawp-rows rendering">
            <?php foreach (range(1, $page_size) as $index): ?>
                <div class="iawp-row">
                    <?php foreach ($all_columns as $column): ?>
                        <?php $class = $column->visible() ? 'cell' : 'cell hide'; ?>
                        <div class="<?php echo esc_attr($class); ?>"
                             data-column="<?php echo esc_attr($column->id()); ?>"
                             data-test-visibility="<?php echo $column->visible() ? 'visible' : 'hidden'; ?>"
                        >
                            <div class="row-number"></div>
                            <span class="cell-content">
                            <span class="skeleton-loader"></span>
                        </span>
                            <span class="animator"></span>
                        </div>
                    <?php endforeach ?>
                </div>
            <?php endforeach ?>
        </div>
    <?php else: ?>
        <?php echo $this->section('rows') ?>
    <?php endif; ?>
</div>
</div>

<div class="pagination">
    <button id="pagination-button" class="iawp-button purple"
            data-report-target="loadMore"
            data-action="report#loadMore"
    >
        <span class="disabled-button-text">
            <?php esc_html_e('Showing All Rows', 'iawp'); ?>
        </span>
        <span class="enabled-button-text">
            <?php printf(esc_html__('Load Next %d Rows', 'iawp'), absint($page_size)); ?>
        </span>
    </button>
</div>
<?php $this->stop() ?>

<?php if ($just_rows == true): ?>
    <?php echo $this->section('rows') ?>
<?php else: ?>
    <?php echo $this->section('table') ?>
<?php endif; ?>
