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

    static public function slugify($text)
    {
        // replace non letter or digits by -
        $text = preg_replace('~[^\pL\d]+~u', '-', $text);

        // transliterate
        $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);

        // remove unwanted characters
        $text = preg_replace('~[^-\w]+~', '', $text);

        // trim
        $text = trim($text, '-');

        // remove duplicate -
        $text = preg_replace('~-+~', '-', $text);

        // lowercase
        $text = strtolower($text);

        if (empty($text)) {
            return 'n-a';
        }

        return $text;
    }

    public function __toString()
    {
        return $this->original;
    }
}
