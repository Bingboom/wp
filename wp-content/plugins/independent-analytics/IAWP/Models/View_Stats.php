<?php

namespace IAWP_SCOPED\IAWP\Models;

trait View_Stats
{
    protected $views;
    protected $prev_period_views;
    protected $visitors;
    protected $prev_period_visitors;
    protected $sessions;
    protected $prev_period_sessions;
    protected $average_session_duration;
    protected $prev_period_average_session_duration;
    protected $average_view_duration;
    protected $prev_period_average_view_duration;
    protected final function set_view_stats($row)
    {
        $this->views = isset($row->views) ? \intval($row->views) : null;
        $this->prev_period_views = isset($row->prev_period_views) ? \intval($row->prev_period_views) : null;
        $this->visitors = isset($row->visitors) ? \intval($row->visitors) : null;
        $this->average_session_duration = isset($row->average_session_duration) ? \intval($row->average_session_duration) : null;
        $this->average_view_duration = isset($row->average_view_duration) ? \intval($row->average_view_duration) : null;
        $this->prev_period_visitors = isset($row->prev_period_visitors) ? \intval($row->prev_period_visitors) : null;
        $this->sessions = isset($row->sessions) ? \intval($row->sessions) : null;
        $this->prev_period_sessions = isset($row->prev_period_sessions) ? \intval($row->prev_period_sessions) : null;
        $this->prev_period_average_session_duration = isset($row->prev_period_average_session_duration) ? \intval($row->prev_period_average_session_duration) : null;
        $this->prev_period_average_view_duration = isset($row->prev_period_average_view_duration) ? \intval($row->prev_period_average_view_duration) : null;
    }
    public final function views()
    {
        return $this->views;
    }
    public final function prev_period_views()
    {
        return $this->prev_period_views;
    }
    public final function views_growth()
    {
        $current = $this->views();
        $previous = $this->prev_period_views();
        if ($current == 0 || $previous == 0) {
            return 0;
        } else {
            return ($current - $previous) / $previous * 100;
        }
    }
    public final function visitors()
    {
        return $this->visitors;
    }
    public final function prev_period_visitors()
    {
        return $this->prev_period_visitors;
    }
    public final function visitors_growth()
    {
        $current = $this->visitors();
        $previous = $this->prev_period_visitors();
        if ($current == 0 || $previous == 0) {
            return 0;
        } else {
            return ($current - $previous) / $previous * 100;
        }
    }
    public final function sessions()
    {
        return $this->sessions;
    }
    public final function prev_period_sessions()
    {
        return $this->prev_period_sessions;
    }
    public final function sessions_growth()
    {
        $current = $this->sessions();
        $previous = $this->prev_period_sessions();
        if ($current == 0 || $previous == 0) {
            return 0;
        } else {
            return ($current - $previous) / $previous * 100;
        }
    }
    public final function average_session_duration()
    {
        return $this->average_session_duration;
    }
    public final function prev_period_average_session_duration()
    {
        return $this->prev_period_average_session_duration;
    }
    public final function average_session_duration_growth()
    {
        $current = $this->average_session_duration();
        $previous = $this->prev_period_average_session_duration();
        if ($current == 0 || $previous == 0) {
            return 0;
        } else {
            return ($current - $previous) / $previous * 100;
        }
    }
    public final function average_view_duration()
    {
        return $this->average_view_duration;
    }
    public final function prev_period_average_view_duration()
    {
        return $this->prev_period_average_view_duration;
    }
    public final function average_view_duration_growth()
    {
        $current = $this->average_view_duration();
        $previous = $this->prev_period_average_view_duration();
        if ($current == 0 || $previous == 0) {
            return 0;
        } else {
            return ($current - $previous) / $previous * 100;
        }
    }
}
