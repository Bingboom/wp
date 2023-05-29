<?php

namespace IAWP_SCOPED\IAWP;

use IAWP_SCOPED\IAWP\Utils\Request;
class Current_Resource
{
    private $type;
    private $meta_key;
    private $meta_value;
    /**
     * @param string $type
     * @param string|null $meta_key
     * @param string|null $meta_value
     */
    private function __construct(string $type, ?string $meta_key = null, ?string $meta_value = null)
    {
        $this->type = $type;
        $this->meta_key = $meta_key;
        $this->meta_value = $meta_value;
    }
    /**
     * @return string
     */
    public function type() : string
    {
        return $this->type;
    }
    /**
     * @return string|null
     */
    public function meta_key() : ?string
    {
        return $this->meta_key;
    }
    /**
     * @return string|null
     */
    public function meta_value() : ?string
    {
        return $this->meta_value;
    }
    /**
     * @return bool
     */
    public function has_meta() : bool
    {
        return !\is_null($this->meta_key) && !\is_null($this->meta_value);
    }
    /**
     * @return self|null
     */
    public static function get_resource() : ?self
    {
        if (\is_singular()) {
            $type = 'singular';
            $meta_key = 'singular_id';
            $meta_value = \get_queried_object_id();
        } elseif (\is_author()) {
            $type = 'author_archive';
            $meta_key = 'author_id';
            $meta_value = \get_queried_object_id();
        } elseif (\is_date()) {
            $type = 'date_archive';
            $meta_key = 'date_archive';
            $meta_value = self::get_date_archive_date();
        } elseif (\is_post_type_archive()) {
            $type = 'post_type_archive';
            $meta_key = 'post_type';
            $meta_value = \get_queried_object()->name;
        } elseif (\is_category() || \is_tag() || \is_tax()) {
            $type = 'term_archive';
            $meta_key = 'term_id';
            $meta_value = \get_queried_object_id();
        } elseif (\is_search()) {
            $type = 'search';
            $meta_key = 'search_query';
            $meta_value = \get_search_query();
        } elseif (\is_home()) {
            $type = 'home';
            $meta_key = null;
            $meta_value = null;
        } elseif (\is_404()) {
            $path = Request::path_relative_to_site_url();
            if (\is_null($path)) {
                return null;
            }
            $type = '404';
            $meta_key = 'not_found_url';
            $meta_value = $path;
        } else {
            return null;
        }
        return new self($type, $meta_key, $meta_value);
    }
    /**
     * @return mixed|string
     */
    private static function get_date_archive_date()
    {
        $str = \get_query_var('year');
        if (\is_month() || \is_day()) {
            $month = \get_query_var('monthnum');
            $str = $str . '-' . \str_pad($month, 2, '0', \STR_PAD_LEFT);
        }
        if (\is_day()) {
            $day = \get_query_var('day');
            $str = $str . '-' . \str_pad($day, 2, '0', \STR_PAD_LEFT);
        }
        return $str;
    }
}
