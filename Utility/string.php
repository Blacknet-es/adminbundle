<?php

namespace Trendix\AdminBundle\Utility;

/*
 * Utilidades con cadenas
 */

class string {
    private $original;
    
    public function __construct($string = '')
    {
        if (!is_string($string)) {
            return $this;
        }
        $this->original = $string;
        return $this;
    }
    
    public function setString ($string)
    {
        $this->original = $string;
        return $this;
    }
    
    public function lenght()
    {
        return strlen($this->original);
    }
    
    public function replace ($keys, $values)
    {
        return str_replace($keys, $values, $this->original);
    }
    
    public function __toString()
    {
        return $this->original;
    }
}
