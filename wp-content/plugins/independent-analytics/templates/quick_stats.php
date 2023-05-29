<?php $class = $is_filtered ? 'quick-stats filtered' : 'quick-stats' ?>
<?php $class .= ' total-of-' . count($stats) ?>
<div id="quick-stats" class="<?php esc_attr_e($class); ?>">
    <?php foreach ($stats as $stat): ?>
        <div class="stat <?php esc_attr_e($stat['class']); ?>">
            <div class="metric">
                <?php esc_html_e($stat['title']); ?>
                <span class="dashicons dashicons-filter"></span>
                <svg class="circle" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">
                    <circle cx="50" cy="50" r="50"/>
                </svg>
            </div>
            <div class="values">
                <span class="count" test-value="<?php esc_attr_e(strip_tags($stat['count'])); ?>">
                    <?php echo wp_kses($stat['count'], ['span' => []]); ?>
                    <?php if ($is_filtered) : ?>
                        <span class="unfiltered"> / <?php esc_html_e($stat['unfiltered']) ?></span>
                    <?php endif; ?>
                </span>
            </div>
            <span class="growth">
                <?php $direction = $stat['growth'] >= 0 ? 'up' : 'down' ?>
                <span class="percentage <?php esc_attr_e($direction) ?>"
                        test-value="<?php esc_attr_e($stat['growth']); ?>">
                        <span class="dashicons dashicons-arrow-up-alt growth-arrow"></span><?php echo absint(number_format($stat['growth'])); ?>%
                </span>
                <span class="period-label"><?php esc_html_e('vs. previous period', 'iawp') ?></span>
            </span>
        </div>
    <?php endforeach; ?>
</div>
