<?php

namespace Trendix\AdminBundle\Utility;

/*
 * Utilidades con cadenas
 */

class StringHelper {
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
        // replace all tildes
        $text = preg_replace("[áàâãª]","a",$text);
        $text = preg_replace("[ÁÀÂÃ]","A",$text);
        $text = preg_replace("[éèê]","e",$text);
        $text = preg_replace("[ÉÈÊ]","E",$text);
        $text = preg_replace("[íìî]","i",$text);
        $text = preg_replace("[ÍÌÎ]","I",$text);
        $text = preg_replace("[óòôõº]","o",$text);
        $text = preg_replace("[ÓÒÔÕ]","O",$text);
        $text = preg_replace("[úùû]","u",$text);
        $text = preg_replace("[ÚÙÛ]","U",$text);
        $text = str_replace(" ","-",$text);
        $text = str_replace("ñ","n",$text);
        $text = str_replace("Ñ","N",$text);

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


    /**
     * @param $length integer Longitud de la cadena
     * @param string $keyspace Caracteres admitidos
     * @return string Cadena aleatoria segura
     */
    public static function generatePassword(
        $length,
        $keyspace = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'
    ) {
        $str = '';
        $max = mb_strlen($keyspace, '8bit') - 1;
        for ($i = 0; $i < $length; ++$i) {
            $str .= $keyspace[random_int(0, $max)];
        }
        return $str;
    }

    public function __toString()
    {
        return $this->original;
    }
}
