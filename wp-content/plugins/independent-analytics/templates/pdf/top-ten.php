<div class="top-ten">
    <div class="heading">
        <div class="title-med"><?php printf(esc_html__('Top 10 %s', 'iawp'), $title); ?></div>
        <div class="views-heading"><?php esc_html_e('Views', 'iawp'); ?></div>
    </div>
    <ol>
        <?php foreach($items as $item): ?>
            <li>
                <span class="title"><?php esc_html_e($item['title']); ?></span>
                <span class="views"><?php echo absint($item['views']); ?></span>
            </li>
        <?php endforeach; ?>
    </ol>
</div>