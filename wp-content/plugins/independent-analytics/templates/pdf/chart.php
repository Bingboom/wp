<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewbox="<?php echo '0 ' . '-' , absint($chart_data['height']) . ' ' . absint($chart_data['width']) . ' ' . absint($chart_data['height']); ?>">
    <g>  
        <!-- X Axis -->
        <?php
        for ($i = 0; $i < count($chart_data['view_coords']); $i++) :
            if (($i + 3) % 3 == 0) : ?>
                <text style="font-size:10px" text-anchor="middle"
                    x="<?php esc_attr_e($chart_data['view_coords'][$i]['x']); ?>" 
                    y="20" ><?php esc_html_e($chart_data['xLabels'][$i]); ?></text>
                <line stroke="#DEDAE6" 
                    x1="<?php esc_attr_e($chart_data['view_coords'][$i]['x']); ?>" 
                    y1="0" 
                    x2="<?php esc_attr_e($chart_data['view_coords'][$i]['x']); ?>" 
                    y2="9" />
            <?php endif;
        endfor; ?>
    </g>
    <g>
        <!-- Y Axis -->
        <line x1="0" y1="0" x2="0" y2="-<?php echo absint($chart_data['height']); ?>" stroke="#DEDAE6" />
        <?php
        foreach ($chart_data['yLabels'] as $label_data) : ?>
            <text style="font-size:10px;" text-anchor="end" 
                x="-10" 
                y="-<?php esc_attr_e($label_data['textY']); ?>"><?php esc_attr_e($label_data['label']); ?></text>
            <line stroke="#DEDAE6"
                x1="-8" y1="-<?php esc_attr_e($label_data['tickY']); ?>" 
                x2="0" y2="-<?php esc_attr_e($label_data['tickY']); ?>" />
            <line stroke="#EEE" stroke-dasharray="2 5" 
                x1="0" y1="-<?php esc_attr_e($label_data['tickY']); ?>" 
                x2="<?php esc_attr_e($chart_data['yEnd']); ?>" y2="-<?php esc_attr_e($label_data['tickY']); ?>" />
        <?php endforeach; ?>
    </g>

    <g>
        <!-- Session Points -->
        <polyline points="<?php echo $chart_data['session_coords_string']; ?>" style="fill:rgba(217,59,41,0.1);stroke:#D93B29;stroke-width:2px;" stroke-linejoin="round" />
        <?php
        for ($i = 0; $i < count($chart_data['session_coords']); $i++) : ?>
            <circle r="3" style="fill:#D93B29"
                cx="<?php esc_attr_e($chart_data['session_coords'][$i]['x']); ?>" 
                cy="-<?php esc_attr_e($chart_data['session_coords'][$i]['y']); ?>"  />
        <?php endfor; ?>
    </g>

    <g>
        <!-- View Points -->
        <polyline points="<?php echo $chart_data['view_coords_string']; ?>" style="fill:rgba(81,35,160,0.1);stroke:#5123A0;stroke-width:2px;" stroke-linejoin="round" />
        <?php
        for ($i = 0; $i < count($chart_data['view_coords']); $i++) : ?>
            <circle r="3" style="fill:#5123A0"
                cx="<?php esc_attr_e($chart_data['view_coords'][$i]['x']); ?>" 
                cy="-<?php esc_attr_e($chart_data['view_coords'][$i]['y']); ?>"  />
        <?php endfor; ?>
    </g>

    <g>  
        <!-- Visitor Points -->
        <polyline points="<?php echo $chart_data['visitor_coords_string']; ?>" style="fill:rgba(246,157,10,0.1);stroke:#F69D0A;stroke-width:2px;" stroke-linejoin="round" />
        <?php
        for ($i = 0; $i < count($chart_data['visitor_coords']); $i++) : ?>
            <circle r="3" style="fill:#F69D0A"
                cx="<?php esc_attr_e($chart_data['visitor_coords'][$i]['x']); ?>" 
                cy="-<?php esc_attr_e($chart_data['visitor_coords'][$i]['y']); ?>"  />
        <?php endfor; ?>
    </g>
</svg>