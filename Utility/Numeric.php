<?php
/**
 * Created by PhpStorm.
 * User: jose
 * Date: 1/10/18
 * Time: 12:33
 */

namespace Trendix\AdminBundle\Utility;

use Symfony\Component\HttpFoundation\Session\Session;

class Numeric
{
    /**
     * @var float
     */
    private $value;

    /**
     * @var Divisa
     */
    private $divisa;

    /**
     * @var String
     */
    private $decimalSymbol;

    /**
     * @var String
     */
    private $thousandSymbol;

    /**
     * @var integer
     */
    private $decimalNumber;

    /**
     * @var Session
     */
    private $sesion;

    protected static $letters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';

    public function __construct($value = null, $divisa = null, $decimales = null)
    {
        $this->value = floatval($value);
        $this->divisa = $divisa;
        $this->sesion = new Session();
        /*
        $sessionSettings = $this->sesion->get(ItbidBaseController::ITBID_SYSTEM_SETTINGS);

        //Coje los valores del sistema para los decimales y el separador de miles
        $this->thousandSymbol = $sessionSettings[ItbidBaseController::ITBID_THOUSANDS_SEPARATOR] != null ? $sessionSettings[ItbidBaseController::ITBID_THOUSANDS_SEPARATOR] : '.';
        $this->decimalSymbol = $sessionSettings[ItbidBaseController::ITBID_DECIMAL_SEPARATOR] != null ? $sessionSettings[ItbidBaseController::ITBID_DECIMAL_SEPARATOR] : ',';

        if(!is_null($decimales)){
            $this->decimalNumber = $decimales;
        }else{
            $this->decimalNumber = $sessionSettings[ItbidBaseController::ITBID_NUMBER_DECIMAL] != null ? $sessionSettings[ItbidBaseController::ITBID_NUMBER_DECIMAL] : 2;
        }
        */
        $this->thousandSymbol = '.';
        $this->decimalSymbol = ',';

        if(!is_null($decimales)){
            $this->decimalNumber = $decimales;
        }else{
            $this->decimalNumber = 2;
        }

    }


    public function FloatToAmount($floor = false)
    {
        if ($this->value == 0) {
            return '0'. $this->decimalSymbol . '0';
        }
        return $this->getStringThousandSeparated() . $this->decimalSymbol . $this->getSplittedParteDecimal($floor);
    }

    public function AmountToFloat($value)
    {
        $value = str_replace($this->thousandSymbol, '', $value);
        $value = str_replace($this->decimalSymbol, '.', $value);
        return floatval($value);
    }

    public static function createFromAmount($amount, $divisa = null)
    {
        $value = null;
        $numeric = new Numeric(0, $divisa);

        if(is_numeric($amount) && !is_string($amount)) {
            $value = $amount;
        } else {
            $amount = strval($amount);
            $value = $numeric->AmountToFloat($amount);
        }
        return $numeric->setValue($value);
    }

    public static function getDecimalNumber()
    {
        $session = new Session();
        // return $session->get(ItbidBaseController::ITBID_SYSTEM_SETTINGS.'/'.ItbidBaseController::ITBID_NUMBER_DECIMAL, 2);
        return 2;
    }

    public function changeDivisa(Divisa $divisa)
    {
        $value = $this->getSystemValue();
        $value = $value / $divisa->getCambioDivisaSistema();
        $this->value = $value;
        $this->divisa = $divisa;
        return $this;
    }

    /**
     * Devuelve el factor de cambio de una divisa a otra
     * @param Divisa $from
     * @param Divisa $to
     * @return float
     */
    public static function getExchange(Divisa $from, Divisa $to)
    {
        return $from->getCambioDivisaSistema() / $to->getCambioDivisaSistema();
    }

    public function setValue($value)
    {
        $this->value = $value;
        return $this;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function getDivisa()
    {
        return $this->divisa;
    }

    public function getSystemValue()
    {
        if (!$this->divisa instanceof Divisa) {
            return $this->value;
        }
        return $this->value * $this->divisa->getCambioDivisaSistema();
    }

    /* Funciones especificas para la tranformación de valores */
    private function getParteEntera()
    {
        $strVal = strval($this->value);
        $exploded = explode('.', $strVal);
        $strVal = $exploded[0];
        return $strVal;
    }

    private function getParteDecimal()
    {
        $parteDecimal = strval(round($this->value - $this->getParteEntera(), $this->decimalNumber));
        $parteDecimal = str_replace('-', '', $parteDecimal);
        return str_replace('0.', '', $parteDecimal);
    }

    private function getStringThousandSeparated()
    {
        $value = $this->getParteEntera();
        $array = array_reverse(str_split($value));

        $result = '';

        $thousandPosition = 3;
        $counter = 0;
        foreach ($array as $a) {
            if ($counter % $thousandPosition == 0 && $counter > 0) {
                $result .= $this->thousandSymbol;
            }

            $result .= $a;
            ++$counter;
        }

        return strrev($result);
    }

    /**
     * @return string
     */
    private function getSplittedParteDecimal($floor = false)
    {
        $resultado = '0.'.$this->getParteDecimal();
        if ($floor){
            $resultado = floor(floatval($resultado) * pow(10, $this->decimalNumber)) / pow(10, $this->decimalNumber);
        }
        else{
            $resultado = round(floatval($resultado), $this->decimalNumber);
        }

        return str_replace('0.', '', $resultado);
    }

    /**
     * @param Proveedor $proveedor
     * @param float $distancia
     * @return float
     */
    static public function getPuntuacionDistancia(Proveedor $proveedor, float $distancia)
    {
        return round(((float)1-(is_null($proveedor->getDistance()) ? 0 : $proveedor->getDistance() /$distancia))*100,2);
    }

    /**
     * Devuelve el código de letras de un número (BASE 25) Si no es númerico devuelve A (0)
     * @param $number
     * @return string
     */
    public static function getLetterFromNumber($number)
    {
        if (!is_numeric($number)) {
            return 'A';
        }
        $code = '';
        $total = strlen(self::$letters);
        if ($number >= $total) {
            return self::getLetterFromNumber(floor($number/$total) - 1).self::$letters[$number % $total];
        } else {
            return $code . self::$letters[$number];
        }
    }
}