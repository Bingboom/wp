<?php

namespace IAWP_SCOPED\IAWP\Models;

class Geo
{
    use View_Stats;
    use WooCommerce_Stats;
    private $continent;
    private $country;
    private $country_code;
    private $subdivision;
    private $city;
    private $visitor_ids;
    public function __construct($row)
    {
        $this->continent = $row->continent;
        $this->country = $row->country;
        $this->country_code = $row->country_code;
        $this->subdivision = $row->subdivision ?? '';
        $this->city = $row->city ?? '';
        $this->visitor_ids = $row->visitor_ids;
        $this->set_view_stats($row);
        $this->set_wc_stats($row);
    }
    protected function ids()
    {
        return $this->visitor_ids;
    }
    public function continent()
    {
        return $this->continent;
    }
    public function country()
    {
        return $this->country;
    }
    public function flag()
    {
        $img_name = \strtolower($this->country_code);
        return \IAWP_SCOPED\iawp_url_to("img/flags/{$img_name}.svg");
    }
    public function subdivision()
    {
        return $this->subdivision;
    }
    public function city()
    {
        return $this->city;
    }
    public static function geo_ids(array $geos) : array
    {
        if (\count($geos) === 0) {
            return [];
        }
        $array_of_arrays = \array_map(function ($geo) {
            return $geo->ids();
        }, $geos);
        return \array_merge(...$array_of_arrays);
    }
}
