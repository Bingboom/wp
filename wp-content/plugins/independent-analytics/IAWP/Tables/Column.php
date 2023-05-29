<?php

namespace IAWP_SCOPED\IAWP\Tables;

class Column
{
    private $id;
    private $label;
    private $visible;
    private $sort_direction;
    private $requires_woocommerce;
    private $exportable;
    public function __construct($options)
    {
        $this->id = $options['id'];
        $this->label = $options['label'];
        $this->visible = $options['visible'];
        $this->sort_direction = $options['sort_direction'];
        $this->requires_woocommerce = $options['requires_woocommerce'] ?? \false;
        $this->exportable = $options['exportable'] ?? \true;
    }
    public function id() : string
    {
        return $this->id;
    }
    public function label() : string
    {
        return $this->label;
    }
    public function visible() : bool
    {
        return $this->visible;
    }
    public function sort_direction() : string
    {
        return $this->sort_direction;
    }
    public function set_visible(bool $visible) : void
    {
        $this->visible = $visible;
    }
    public function requires_woocommerce() : bool
    {
        return $this->requires_woocommerce;
    }
    public function exportable() : bool
    {
        return $this->exportable;
    }
}
