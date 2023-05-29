<?php

namespace IAWP_SCOPED\IAWP\Models;

trait WooCommerce_Stats
{
    protected $wc_orders;
    protected $wc_gross_sales;
    protected $wc_refunds;
    protected $wc_refunded_amount;
    protected final function set_wc_stats($row)
    {
        $this->wc_orders = \floatval($row->wc_orders);
        $this->wc_gross_sales = \floatval($row->wc_gross_sales);
        $this->wc_refunds = \floatval($row->wc_refunds);
        $this->wc_refunded_amount = \floatval($row->wc_refunded_amount);
    }
    public final function wc_orders() : int
    {
        return $this->wc_orders;
    }
    public final function wc_gross_sales()
    {
        return $this->wc_gross_sales;
    }
    public final function wc_refunds()
    {
        return $this->wc_refunds;
    }
    public function wc_refunded_amount()
    {
        return $this->wc_refunded_amount;
    }
    public function wc_net_sales()
    {
        return $this->wc_gross_sales - $this->wc_refunded_amount;
    }
    public function woocommerce_conversion_rate()
    {
        $orders = $this->wc_orders();
        $visitors = $this->visitors();
        if ($visitors === 0) {
            return 0;
        }
        return $orders / $visitors * 100;
    }
    public function woocommerce_earnings_per_visitor()
    {
        $net_sales = $this->wc_net_sales();
        $visitors = $this->visitors();
        if ($visitors === 0) {
            return 0;
        }
        return $net_sales / $visitors;
    }
    public function woocommerce_average_order_volume()
    {
        $gross_sales = $this->wc_gross_sales();
        $orders = $this->wc_orders();
        if ($orders === 0) {
            return 0;
        }
        return $gross_sales / $orders;
    }
}
