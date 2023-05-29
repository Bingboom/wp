<?php

namespace IAWP_SCOPED\IAWP\Models;

use IAWP_SCOPED\IAWP\Query;
use IAWP_SCOPED\IAWP\Utils\Request;
abstract class Page
{
    use View_Stats;
    private $id;
    private $resource;
    private $is_deleted;
    private $cache;
    private $cached_title;
    private $cached_type;
    private $cached_type_label;
    private $cached_icon;
    private $cached_author_id;
    private $cached_author;
    private $cached_avatar;
    private $cached_date;
    private $cached_category;
    public function __construct($row)
    {
        $this->id = $row->id ?? null;
        $this->resource = $row->resource ?? null;
        $this->set_view_stats($row);
    }
    /**
     * By default, pages don't have the ability to have comments.
     * This can be overridden by a subclass to return an actual comments value.
     *
     * @return int|null
     */
    public function comments() : ?int
    {
        return null;
    }
    public final function is_resource_type($type) : bool
    {
        return $this->resource == $type;
    }
    public final function is_deleted() : bool
    {
        if (!\is_null($this->is_deleted)) {
            return $this->is_deleted;
        }
        $this->is_deleted = $this->calculate_is_deleted();
        return $this->is_deleted;
    }
    private function use_cache() : bool
    {
        if (!\is_null($this->cache)) {
            return \true;
        }
        $deleted = $this->is_deleted();
        if ($deleted) {
            $this->cache = $this->get_cache();
        }
        return $deleted;
    }
    private function get_cache()
    {
        global $wpdb;
        $resources_table = Query::get_table_name(Query::RESOURCES);
        $resource_key = $this->resource_key();
        $resource_value = $this->resource_value();
        $query = $wpdb->prepare("SELECT * FROM {$resources_table} WHERE {$resource_key} = %s", $resource_value);
        return $wpdb->get_row($query);
    }
    public final function update_cache() : void
    {
        global $wpdb;
        $resources_table = Query::get_table_name(Query::RESOURCES);
        $resource_key = $this->resource_key();
        $resource_value = $this->resource_value();
        $query = $wpdb->prepare("UPDATE {$resources_table} SET cached_title = %s, cached_url = %s, cached_type = %s, cached_type_label = %s, cached_author_id = %s, cached_author = %s, cached_date = %s, cached_category = %s WHERE {$resource_key} = %s", $this->calculate_title(), $this->calculate_url(), $this->calculate_type(), $this->calculate_type_label(), $this->calculate_author_id(), $this->calculate_author(), $this->calculate_date(), \implode(', ', $this->calculate_category()), $resource_value);
        $wpdb->query($query);
    }
    public final function id()
    {
        return $this->id;
    }
    // The goal here is to generate a unique resource key that is *not* the url. This is for internal comparison
    // purpose only. So a 404 page with be something like not_found_/test/abc and a term archive would be term_12.
    protected final function unique_resource_id()
    {
        return $this->resource . '_' . $this->resource_value();
    }
    public final function url($full_url = \false)
    {
        if ($this->use_cache()) {
            $url = $this->cache->cached_url;
        } else {
            $url = $this->calculate_url();
        }
        if ($full_url) {
            return $url;
        } else {
            return Request::path_relative_to_site_url($url);
        }
    }
    public final function title()
    {
        if ($this->use_cache()) {
            return $this->cache->cached_title;
        }
        if (\is_null($this->cached_title)) {
            $this->cached_title = $this->calculate_title();
        }
        return \strlen($this->cached_title) > 0 ? $this->cached_title : '(no title)';
    }
    public final function type($raw = \false)
    {
        if ($raw) {
            if ($this->use_cache()) {
                return $this->cache->cached_type;
            }
            if (\is_null($this->cached_type)) {
                $this->cached_type = $this->calculate_type();
            }
            return $this->cached_type;
        } else {
            if ($this->use_cache()) {
                return $this->cache->cached_type_label;
            }
            if (\is_null($this->cached_type_label)) {
                $this->cached_type_label = $this->calculate_type_label();
            }
            return $this->cached_type_label;
        }
    }
    public final function icon()
    {
        if (\is_null($this->cached_icon)) {
            $this->cached_icon = $this->calculate_icon();
        }
        return $this->cached_icon;
    }
    public final function author()
    {
        if ($this->use_cache()) {
            return $this->cache->cached_author;
        }
        if (\is_null($this->cached_author)) {
            $this->cached_author = $this->calculate_author();
        }
        return $this->cached_author;
    }
    public final function author_id()
    {
        if ($this->use_cache()) {
            return $this->cache->cached_author_id;
        }
        if (\is_null($this->cached_author_id)) {
            $this->cached_author_id = $this->calculate_author_id();
        }
        return $this->cached_author_id;
    }
    public final function avatar()
    {
        if (\is_null($this->cached_avatar)) {
            $this->cached_avatar = $this->calculate_avatar();
        }
        return $this->cached_avatar;
    }
    public final function date()
    {
        if (\is_null($this->cached_date)) {
            $this->cached_date = $this->calculate_date();
        }
        return $this->cached_date;
    }
    public final function formatted_category() : ?string
    {
        $categories = $this->category(\false);
        $category_names = [];
        if (\count($categories) === 0) {
            return null;
        }
        foreach ($categories as $category_id) {
            $category = \get_the_category_by_ID($category_id);
            if (!\is_wp_error($category)) {
                $category_names[] = $category;
            }
        }
        return \implode(', ', $category_names);
    }
    public final function category($formatted = \true)
    {
        if ($formatted === \true) {
            return $this->formatted_category();
        }
        if (\is_null($this->cached_category)) {
            $this->cached_category = $this->calculate_category();
        }
        return $this->cached_category;
    }
    public function most_popular_subtitle() : ?string
    {
        return null;
    }
    //
    // Below are required and optional methods that classes extending page can define
    //
    protected abstract function resource_key();
    protected abstract function resource_value();
    protected abstract function calculate_is_deleted() : bool;
    protected abstract function calculate_url();
    protected abstract function calculate_title();
    protected abstract function calculate_type();
    protected abstract function calculate_type_label();
    protected abstract function calculate_icon();
    protected abstract function calculate_author();
    protected abstract function calculate_author_id();
    protected abstract function calculate_avatar();
    protected abstract function calculate_date();
    protected abstract function calculate_category();
}
