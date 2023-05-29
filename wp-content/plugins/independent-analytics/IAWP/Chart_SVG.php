<?php

namespace IAWP_SCOPED\IAWP;

use IAWP_SCOPED\IAWP\Statistics\Statistics;
class Chart_SVG
{
    private $width;
    private $height;
    private $views;
    private $daily_views;
    private $daily_visitors;
    private $daily_sessions;
    private $x_labels;
    private $y_labels;
    private $y_label_highest;
    private $x_step;
    private $y_step;
    private $view_coords;
    private $visitor_coords;
    private $session_coords;
    public function __construct(Statistics $views, $width, $height)
    {
        $this->views = $views;
        $this->width = $width;
        $this->height = $height;
        $this->daily_views = \array_map(function ($data_point) {
            return $data_point[1];
        }, $this->views->daily_views());
        $this->daily_visitors = \array_map(function ($data_point) {
            return $data_point[1];
        }, $this->views->daily_visitors());
        $this->daily_sessions = \array_map(function ($data_point) {
            return $data_point[1];
        }, $this->views->daily_sessions());
        $this->x_labels = \array_map(function ($data_point) {
            return $data_point[0]->format('M j');
        }, $this->views->daily_views());
        $this->y_labels = $this->get_y_labels();
        $this->x_step = $this->width / \count($this->daily_views);
        $this->y_step = $this->height / $this->y_label_highest;
        $this->view_coords = $this->get_point_coordinates($this->daily_views);
        $this->visitor_coords = $this->get_point_coordinates($this->daily_visitors);
        $this->session_coords = $this->get_point_coordinates($this->daily_sessions);
    }
    public function get_svg_data()
    {
        $svg_data = ['width' => $this->width, 'height' => $this->height, 'xLabels' => $this->x_labels, 'yLabels' => $this->y_labels, 'yEnd' => $this->width - $this->x_step, 'view_coords' => $this->view_coords, 'view_coords_string' => $this->get_polyline_coords($this->view_coords), 'visitor_coords' => $this->visitor_coords, 'visitor_coords_string' => $this->get_polyline_coords($this->visitor_coords), 'session_coords' => $this->session_coords, 'session_coords_string' => $this->get_polyline_coords($this->session_coords)];
        return $svg_data;
    }
    private function get_y_labels()
    {
        $most_views = \max($this->daily_views);
        $y_label_step = \round($most_views / 10);
        // Prevent a step equal to zero
        $y_label_step = $y_label_step == 0 ? 1 : $y_label_step;
        $total_steps = \round($most_views / $y_label_step) + 1;
        $this->y_label_highest = $y_label_step * $total_steps;
        $y_labels[] = ['tickY' => 0, 'textY' => -3, 'label' => 0];
        for ($i = 1; $i <= $total_steps; $i++) {
            $y = $this->height / $total_steps * $i;
            $y_labels[] = ['tickY' => $y, 'textY' => $y - 3, 'label' => $y_label_step * $i];
        }
        return $y_labels;
    }
    private function get_point_coordinates(array $views) : array
    {
        $coordinates = [];
        for ($i = 0; $i < \count($views); $i++) {
            $x = $i * $this->x_step;
            $y = $views[$i] * $this->y_step;
            $coordinates[] = ['x' => $x, 'y' => $y];
        }
        return $coordinates;
    }
    // The Y point is flipped because SVGs place (0,0) at the top-left, rather than the bottom-left
    private function get_polyline_coords(array $coordinates) : string
    {
        $points = '';
        foreach ($coordinates as $coordinate) {
            $points .= $coordinate['x'] . ',-' . $coordinate['y'] . ' ';
        }
        $points .= $this->width - $this->x_step . ',0 ';
        $points .= '0,0';
        return $points;
    }
}
