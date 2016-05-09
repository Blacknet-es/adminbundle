<?php

namespace Trendix\AdminBundle\Utility;

class Color
{
    private $color;
    private $type;
    private $elem1;
    private $elem2;
    private $elem3;

    public function __construct($color1 = '', $color2 = '', $color3 = '', $tipo = 'hex')
    {
        $this->setColor(array($tipo, $color1, $color2, $color3));
    }

    /* COLOR TRANSFORM */
    public function Spin($delta) {
        $hsl = $this->toHSL($this->color);

        $hsl[1] = $hsl[1] + $delta % 360;
        if ($hsl[1] < 0) $hsl[1] += 360;

        $this->setColor($this->toRGB($hsl));
    }

    public function getGama()
    {
        $colores = array();

        $primaries = array(
            array('color', 255, 0 , 0),
            array('color', 200, 200 , 50),
            array('color', 255, 150 , 200),
        );

        foreach ($primaries as $primary) {
            $this->setColor($primary);
            for ($i = 1; $i <= 9; ++$i) {
                $colores[] = clone $this;
                $this->Spin(200);
            }
        }

        return $colores;
    }

    public function getColorFromGama($number)
    {
        $gama = $this->getGama();
        if (isset($gama[$number])) {
            return $gama[$number];
        }

        return $gama[0];
    }

    /* BASE FUNCTIONS */

    protected function setColor($color)
    {
        $this->color = $color;
        $this->setElements($color);
    }

    public function getRGB()
    {
        return $this->toRGB($this->color);
    }

    public function getHSL()
    {
        return $this->toHSL($this->color);
    }

    public function getHEX()
    {
        return '';
    }

    public function getCSS()
    {
        $rgb = $this->toRGB($this->color);
        return 'rgb('.intval($rgb[1]).', '.intval($rgb[2]).', '.intval($rgb[3]).')';
    }

    protected function setElements($color)
    {
        $this->type = $color[0];
        $this->elem1 = $color[1];
        $this->elem2 = $color[2];
        $this->elem3 = $color[3];
    }

    protected function toHSL($color) {
        if ($color[0] == 'hsl') return $color;

        $r = $color[1] / 255;
        $g = $color[2] / 255;
        $b = $color[3] / 255;

        $min = min($r, $g, $b);
        $max = max($r, $g, $b);

        $L = ($min + $max) / 2;
        if ($min == $max) {
            $S = $H = 0;
        } else {
            if ($L < 0.5)
                $S = ($max - $min)/($max + $min);
            else
                $S = ($max - $min)/(2.0 - $max - $min);

            if ($r == $max) $H = ($g - $b)/($max - $min);
            elseif ($g == $max) $H = 2.0 + ($b - $r)/($max - $min);
            elseif ($b == $max) $H = 4.0 + ($r - $g)/($max - $min);

        }

        $out = array('hsl',
            ($H < 0 ? $H + 6 : $H)*60,
            $S*100,
            $L*100,
        );

        if (count($color) > 4) $out[] = $color[4]; // copy alpha
        return $out;
    }

    protected function toRGB_helper($comp, $temp1, $temp2) {
        if ($comp < 0) $comp += 1.0;
        elseif ($comp > 1) $comp -= 1.0;

        if (6 * $comp < 1) return $temp1 + ($temp2 - $temp1) * 6 * $comp;
        if (2 * $comp < 1) return $temp2;
        if (3 * $comp < 2) return $temp1 + ($temp2 - $temp1)*((2/3) - $comp) * 6;

        return $temp1;
    }

    /**
     * Converts a hsl array into a color value in rgb.
     * Expects H to be in range of 0 to 360, S and L in 0 to 100
     */
    protected function toRGB($color) {
        if ($color[0] == 'color') return $color;

        $H = $color[1] / 360;
        $S = $color[2] / 100;
        $L = $color[3] / 100;

        if ($S == 0) {
            $r = $g = $b = $L;
        } else {
            $temp2 = $L < 0.5 ?
                $L*(1.0 + $S) :
                $L + $S - $L * $S;

            $temp1 = 2.0 * $L - $temp2;

            $r = $this->toRGB_helper($H + 1/3, $temp1, $temp2);
            $g = $this->toRGB_helper($H, $temp1, $temp2);
            $b = $this->toRGB_helper($H - 1/3, $temp1, $temp2);
        }

        // $out = array('color', round($r*255), round($g*255), round($b*255));
        $out = array('color', $r*255, $g*255, $b*255);
        if (count($color) > 4) $out[] = $color[4]; // copy alpha
        return $out;
    }

    protected function clamp($v, $max = 1, $min = 0) {
        return min($max, max($min, $v));
    }

    /**
     * Convert the rgb, rgba, hsl color literals of function type
     * as returned by the parser into values of color type.
     */
    protected function funcToColor($func) {
        $fname = $func[1];
        if ($func[2][0] != 'list') return false; // need a list of arguments
        $rawComponents = $func[2][2];

        if ($fname == 'hsl' || $fname == 'hsla') {
            $hsl = array('hsl');
            $i = 0;
            foreach ($rawComponents as $c) {
                $val = $this->reduce($c);
                $val = isset($val[1]) ? floatval($val[1]) : 0;

                if ($i == 0) $clamp = 360;
                elseif ($i < 3) $clamp = 100;
                else $clamp = 1;

                $hsl[] = $this->clamp($val, $clamp);
                $i++;
            }

            while (count($hsl) < 4) $hsl[] = 0;
            return $this->toRGB($hsl);

        } elseif ($fname == 'rgb' || $fname == 'rgba') {
            $components = array();
            $i = 1;
            foreach	($rawComponents as $c) {
                $c = $this->reduce($c);
                if ($i < 4) {
                    if ($c[0] == "number" && $c[2] == "%") {
                        $components[] = 255 * ($c[1] / 100);
                    } else {
                        $components[] = floatval($c[1]);
                    }
                } elseif ($i == 4) {
                    if ($c[0] == "number" && $c[2] == "%") {
                        $components[] = 1.0 * ($c[1] / 100);
                    } else {
                        $components[] = floatval($c[1]);
                    }
                } else break;

                $i++;
            }
            while (count($components) < 3) $components[] = 0;
            array_unshift($components, 'color');
            return $this->fixColor($components);
        }

        return false;
    }

    protected function reduce($value, $forExpression = false) {
        switch ($value[0]) {
            case "interpolate":
                $reduced = $this->reduce($value[1]);
                $var = $this->compileValue($reduced);
                $res = $this->reduce(array("variable", $this->vPrefix . $var));

                if ($res[0] == "raw_color") {
                    $res = $this->coerceColor($res);
                }

                if (empty($value[2])) $res = $this->lib_e($res);

                return $res;
            case "variable":
                $key = $value[1];
                if (is_array($key)) {
                    $key = $this->reduce($key);
                    $key = $this->vPrefix . $this->compileValue($this->lib_e($key));
                }

                $seen =& $this->env->seenNames;

                if (!empty($seen[$key])) {
                    $this->throwError("infinite loop detected: $key");
                }

                $seen[$key] = true;
                $out = $this->reduce($this->get($key));
                $seen[$key] = false;
                return $out;
            case "list":
                foreach ($value[2] as &$item) {
                    $item = $this->reduce($item, $forExpression);
                }
                return $value;
            case "expression":
                return $this->evaluate($value);
            case "string":
                foreach ($value[2] as &$part) {
                    if (is_array($part)) {
                        $strip = $part[0] == "variable";
                        $part = $this->reduce($part);
                        if ($strip) $part = $this->lib_e($part);
                    }
                }
                return $value;
            case "escape":
                list(,$inner) = $value;
                return $this->lib_e($this->reduce($inner));
            case "function":
                $color = $this->funcToColor($value);
                if ($color) return $color;

                list(, $name, $args) = $value;
                if ($name == "%") $name = "_sprintf";

                $f = isset($this->libFunctions[$name]) ?
                    $this->libFunctions[$name] : array($this, 'lib_'.str_replace('-', '_', $name));

                if (is_callable($f)) {
                    if ($args[0] == 'list')
                        $args = self::compressList($args[2], $args[1]);

                    $ret = call_user_func($f, $this->reduce($args, true), $this);

                    if (is_null($ret)) {
                        return array("string", "", array(
                            $name, "(", $args, ")"
                        ));
                    }

                    // convert to a typed value if the result is a php primitive
                    if (is_numeric($ret)) $ret = array('number', $ret, "");
                    elseif (!is_array($ret)) $ret = array('keyword', $ret);

                    return $ret;
                }

                // plain function, reduce args
                $value[2] = $this->reduce($value[2]);
                return $value;
            case "unary":
                list(, $op, $exp) = $value;
                $exp = $this->reduce($exp);

                if ($exp[0] == "number") {
                    switch ($op) {
                        case "+":
                            return $exp;
                        case "-":
                            $exp[1] *= -1;
                            return $exp;
                    }
                }
                return array("string", "", array($op, $exp));
        }

        if ($forExpression) {
            switch ($value[0]) {
                case "keyword":
                    if ($color = $this->coerceColor($value)) {
                        return $color;
                    }
                    break;
                case "raw_color":
                    return $this->coerceColor($value);
            }
        }

        return $value;
    }


    // coerce a value for use in color operation
    protected function coerceColor($value) {
        switch($value[0]) {
            case 'color': return $value;
            case 'raw_color':
                $c = array("color", 0, 0, 0);
                $colorStr = substr($value[1], 1);
                $num = hexdec($colorStr);
                $width = strlen($colorStr) == 3 ? 16 : 256;

                for ($i = 3; $i > 0; $i--) { // 3 2 1
                    $t = $num % $width;
                    $num /= $width;

                    $c[$i] = $t * (256/$width) + $t * floor(16/$width);
                }

                return $c;
            case 'keyword':
                $name = $value[1];
                if (isset(self::$cssColors[$name])) {
                    $rgba = explode(',', self::$cssColors[$name]);

                    if(isset($rgba[3]))
                        return array('color', $rgba[0], $rgba[1], $rgba[2], $rgba[3]);

                    return array('color', $rgba[0], $rgba[1], $rgba[2]);
                }
                return null;
        }
    }

    // make something string like into a string
    protected function coerceString($value) {
        switch ($value[0]) {
            case "string":
                return $value;
            case "keyword":
                return array("string", "", array($value[1]));
        }
        return null;
    }

    // turn list of length 1 into value type
    protected function flattenList($value) {
        if ($value[0] == "list" && count($value[2]) == 1) {
            return $this->flattenList($value[2][0]);
        }
        return $value;
    }

    public function toBool($a) {
        if ($a) return self::$TRUE;
        else return self::$FALSE;
    }

    public function __toString()
    {
        return $this->getCSS();
    }
}