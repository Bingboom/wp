<?php

namespace IAWP_SCOPED\IAWP\Utils;

class Option
{
    const STRING = 'string';
    const BOOLEAN = 'boolean';
    const BOOL = 'boolean';
    const INT = 'integer';
    const INTEGER = 'integer';
    const FLOAT = 'float';
    const DOUBLE = 'float';
    const ARRAY = 'array';
    private static $supported_types = ['string', 'boolean', 'integer', 'float', 'array'];
    /**
     * @var array
     */
    private static $registered_options = [];
    /**
     * @var string
     */
    protected $option_name;
    /**
     * @var string
     */
    private $type;
    /**
     * @var mixed|null
     */
    private $default_value;
    /**
     * @var boolean
     */
    private $allow_empty_string = \false;
    public function __construct(string $option_name, string $type, $default_value = null, array $options = [])
    {
        $this->option_name = $option_name;
        $this->type = $type;
        $this->default_value = $default_value;
        if (\array_key_exists('allow_empty_string', $options)) {
            $this->allow_empty_string = \filter_var($options['allow_empty_string'], \FILTER_VALIDATE_BOOLEAN);
        }
        // Throw an error if the type is unsupported
        if (!\in_array($type, self::$supported_types)) {
            throw new \Exception("{$type} is not a supported type");
        }
        // Throw an error if the default_value is set, but does not match the options types
        if (!\is_null($default_value) && $type !== $this->get_type($default_value)) {
            throw new \Exception("Default value for {$option_name} is not of type {$type}");
        }
        // Throw an error if option name is already registered
        foreach (self::$registered_options as $option) {
            if ($option->option_name === $option_name) {
                throw new \Exception("Option {$option_name} is already registered");
            }
        }
        self::$registered_options[] = $this;
    }
    /**
     * @param $value
     *
     * @return string
     */
    private function get_type($value) : string
    {
        $type = \gettype($value);
        if ($type === 'double') {
            return 'float';
        }
        return $type;
    }
    /**
     * @param mixed $value
     *
     * @return mixed
     */
    private function convert_to_wordpress($value)
    {
        switch ($this->type) {
            case 'boolean':
                return $value === \true ? 1 : 0;
        }
        return $value;
    }
    /**
     * @param mixed $value
     *
     * @return mixed
     */
    private function convert_from_wordpress($value)
    {
        switch ($this->type) {
            case 'integer':
                /**
                 * If the stored value is not parsable as an integer, use the default value instead
                 */
                $filtered_value = \filter_var($value, \FILTER_VALIDATE_INT);
                return $filtered_value !== \false ? $filtered_value : $this->default_value;
            case 'float':
                /**
                 * If the stored value is not parsable as an float, use the default value instead
                 */
                $filtered_value = \filter_var($value, \FILTER_VALIDATE_FLOAT);
                return $filtered_value !== \false ? $filtered_value : $this->default_value;
            case 'boolean':
                return \filter_var($value, \FILTER_VALIDATE_BOOLEAN);
            case 'array':
                /**
                 * If an array was saved with update_option, then get_option would return a parsed
                 * array. If get_option returns a string, that means the value wasn't a parsable
                 * array and the default value should be used.
                 */
                return $this->get_type($value) === 'string' ? $this->default_value : $value;
            case 'string':
                if ($value === '' && !$this->allow_empty_string) {
                    return $this->default_value;
                }
                return $value;
        }
        return $value;
    }
    public function set($new_value = null)
    {
        if (\is_null($new_value)) {
            $this->delete();
            return;
        }
        $new_value_type = $this->get_type($new_value);
        if ($this->type !== $new_value_type) {
            throw new \Exception("{$this->option_name} expects type {$this->type} but {$new_value_type} was provided");
        }
        if ($new_value === '' && !$this->allow_empty_string) {
            $this->delete();
            return;
        }
        \update_option($this->option_name, $this->convert_to_wordpress($new_value));
    }
    public function get()
    {
        $value = \get_option($this->option_name, new \Exception());
        if ($value instanceof \Exception) {
            return $this->default_value;
        }
        return $this->convert_from_wordpress($value);
    }
    public function delete()
    {
        \delete_option($this->option_name);
    }
    public static function find($option_name)
    {
        foreach (Option::$registered_options as $option) {
            if ($option->option_name === $option_name) {
                return $option;
            }
        }
        throw new \Exception("Option {$option_name} is not registered");
    }
    public static function set_option($option_name, $value = null)
    {
        $option = self::find($option_name);
        $option->set($value);
    }
    public static function get_option($option_name)
    {
        $option = self::find($option_name);
        return $option->get();
    }
    public static function delete_option($option_name)
    {
        $option = self::find($option_name);
        return $option->delete();
    }
}
