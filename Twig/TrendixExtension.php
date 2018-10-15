<?php

namespace Trendix\AdminBundle\Twig;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\PersistentCollection;
use Trendix\AdministracionBundle\Controller\TrendixBaseController;
use Trendix\AdministracionBundle\Utility\Dates;
use Trendix\AdministracionBundle\Utility\Numeration;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Validator\Constraints\Date;

class TrendixExtension extends \Twig_Extension
{
    /**
     * CONSTANTES
     */
    const SESSION_SYMFONY_TRENDIX        = 'sf_trendix';
    const TRENDIX_TRANSLATIONS        = 'translations';
    const TRENDIX_FORMATS                = 'formats';
    const TRENDIX_DATE_FORMAT            = 'date_format';

    /**
     * Usar las de TrendixBaseController
     * @deprecated
     */
    const TRENDIX_DECIMAL_SEPARATOR    = 'DECIMAL_SEPARATOR';

    /**
     * Usar las de TrendixBaseController
     * @deprecated
     */
    const TRENDIX_THOUSANDS_SEPARATOR    = 'THOUSANDS_SEPARATOR';

    /**
     * @var Router
     */
    private $router;

    /**
     * @var Session
     */
    private $session;

    use ContainerAwareTrait;

    /**
     * Constructor
     */
    public function __construct(Router $router)
    {
        $this->router = $router;
        $this->session = new Session();
    }

    /**
     * Devuelve el nombre de la extensión.
     *
     * @return string El nombre de la extensión
     */
    public function getName()
    {
        return 'trendix';
    }

    /**
     * Devuelve la lista de funciones de la extensión.
     *
     * @return array La lista de funciones
     */
    public function getFunctions()
    {
        return array(
            'getTrendixFormat'                => new \Twig_SimpleFunction('getTrendixFormat', array($this, 'getTrendixFormat')),
            'getTrendixDateFormat'            => new \Twig_SimpleFunction('getTrendixDateFormat', array($this, 'getTrendixDateFormat')),
            'getTrendixDateFormatForJS'        => new \Twig_SimpleFunction('getTrendixDateFormatForJS', array($this, 'getTrendixDateFormatForJS')),
            'getTrendixDateFormatForTwig'        => new \Twig_SimpleFunction('getTrendixDateFormatForTwig', array($this, 'getTrendixDateFormatForTwig')),
            'getTrendixDateTimeFormat'        => new \Twig_SimpleFunction('getTrendixDateTimeFormat', array($this, 'getTrendixDateTimeFormat')),
            'getTrendixDateTimeFormatForJS'    => new \Twig_SimpleFunction('getTrendixDateTimeFormatForJS', array($this, 'getTrendixDateTimeFormatForJS')),
            'getTrendixDateTimeFormatForTwig'    => new \Twig_SimpleFunction('getTrendixDateTimeFormatForTwig', array($this, 'getTrendixDateTimeFormatForTwig')),
            'getTrendixDecimalSeparator'        => new \Twig_SimpleFunction('getTrendixDecimalSeparator', array($this, 'getTrendixDecimalSeparator')),
            'getTrendixThousandsSeparator'    => new \Twig_SimpleFunction('getTrendixThousandsSeparator', array($this, 'getTrendixThousandsSeparator')),
            'getTrendixUrl'                    => new \Twig_SimpleFunction('getTrendixUrl', array($this, 'getTrendixUrl')),
            'getIsNumeric'                    => new \Twig_SimpleFunction('getIsNumeric', array($this, 'getIsNumeric')),
            'getIsString'                    => new \Twig_SimpleFunction('getIsString', array($this, 'getIsString')),
            'getIsDate'                    => new \Twig_SimpleFunction('getIsDate', array($this, 'getIsDate')),
            'ksort'                            => new \Twig_SimpleFunction('ksort', array($this, 'ksort')),
            'getclass'                      => new \Twig_SimpleFunction('getclass', array($this, 'getclass')),
            'camelcase'                      => new \Twig_SimpleFunction('camelcase', array($this, 'camelcase')),
            'timeago'                      => new \Twig_SimpleFunction('timeago', array($this, 'timeago')),
            'letter'                      => new \Twig_SimpleFunction('letter', array($this, 'letter'))
        );
    }

    /**
     * Devuelve la lista de filtros de la extensión
     *
     * @return array La lista de filtros
     */
    public function getFilters()
    {
        return array(
            'formatTrendixDateTime'    => new \Twig_SimpleFilter('formatTrendixDateTime', array($this, 'formatTrendixDateTime')),
            'formatTrendixDate'        => new \Twig_SimpleFilter('formatTrendixDate', array($this, 'formatTrendixDate')),
            'formatTrendixTime'        => new \Twig_SimpleFilter('formatTrendixTime', array($this, 'formatTrendixTime')),
            'formatTrendixNumber'        => new \Twig_SimpleFilter('formatTrendixNumber', array($this, 'formatTrendixNumber')),
            'formatTrendixAmount'        => new \Twig_SimpleFilter('formatTrendixAmount', array($this, 'formatTrendixAmount')),
            'formatLongText'        => new \Twig_SimpleFilter('formatLongText', array($this, 'formatLongText')),
            'formatTextList'        => new \Twig_SimpleFilter('formatTextList', array($this, 'formatTextList')),
            'sortCollection'        => new \Twig_SimpleFilter('sortCollection', array($this, 'sortCollection')),
            'isString'                => new \Twig_SimpleFilter('isString', array($this, 'isString')),
            'isNumeric'                => new \Twig_SimpleFilter('isNumeric', array($this, 'isNumeric')),
            'isDate'                => new \Twig_SimpleFilter('isDate', array($this, 'isDate')),
            'ksort'                    => new \Twig_SimpleFilter('ksort', array($this, 'ksort')),
            'getclass'              => new \Twig_SimpleFilter('getclass', array($this, 'getclass')),
            'camelcase'              => new \Twig_SimpleFilter('camelcase', array($this, 'camelcase')),
            'timeago'              => new \Twig_SimpleFilter('timeago', array($this, 'timeago')),
            'letter'              => new \Twig_SimpleFilter('letter', array($this, 'letter')),
        );
    }

    /**
     * Devuelve un formato de Trendix: fecha, separador decimal, separador de miles, ...
     *
     * @param string $name        Formato: "date_format", "decimal_separator, "thousands_separator", ...
     * @param string $default    Valor por defecto del formato.
     * @return string            Formato de fecha, separador decimal, separador de miles, ...
     */
    public function getTrendixFormat($name, $default)
    {
        return $this->session->get(self::TRENDIX_FORMATS.'/'.$name, $default);
    }

    /**
     * Devuelve el formato de fecha PHP a usar.
     *
     * @param string $default    Valor por defecto.
     * @return string            Formato de fecha PHP.
     */
    public function getTrendixDateFormat($default = 'Y-m-d')
    {
        return $this->session->get(self::TRENDIX_DATE_FORMAT, $default);
    }

    /**
     * Devuelve el formato de fecha Javascript a usar.
     *
     * @return string            Formato de fecha Javascript.
     */
    public function getTrendixDateFormatForJS()
    {
        $dateFormat = $this->getTrendixDateFormat();
        if($dateFormat) {
            $dateFormat = str_replace('d', 'dd', $dateFormat);
            $dateFormat = str_replace('m', 'mm', $dateFormat);
            $dateFormat = str_replace('Y', 'yyyy', $dateFormat);
        }
        return $dateFormat;
    }

    /**
     * Devuelve el formato de fecha Twig a usar.
     *
     * @return string            Formato de fecha Twig.
     */
    public function getTrendixDateFormatForTwig()
    {
        $dateFormat = $this->getTrendixDateFormat();
        if($dateFormat) {
            $dateFormat = str_replace('d', 'dd', $dateFormat);
            $dateFormat = str_replace('m', 'MM', $dateFormat);
            $dateFormat = str_replace('Y', 'yyyy', $dateFormat);
        }
        return $dateFormat;
    }

    /**
     * Devuelve el formato de fecha y hora PHP a usar.
     *
     * @return string            Formato de fecha y hora PHP.
     */
    public function getTrendixDateTimeFormat()
    {
        return $this->getTrendixDateFormat() . " H:i:s";
    }

    /**
     * Devuelve el formato de fecha y hora PHP a usar.
     *
     * @return string            Formato de fecha y hora PHP.
     */
    public function getTrendixTimeFormat()
    {
        return "H:i:s";
    }

    /**
     * Devuelve el formato de fecha y hora Javascript a usar.
     *
     * @return string            Formato de fecha y hora Javascript.
     */
    public function getTrendixDateTimeFormatForJS()
    {
        return $this->getTrendixDateFormatForJS() . " hh:mm:ss";
    }

    /**
     * Devuelve el formato de fecha y hora Twig a usar.
     *
     * @return string            Formato de fecha y hora Twig.
     */
    public function getTrendixDateTimeFormatForTwig()
    {
        return $this->getTrendixDateFormatForTwig() . " HH:mm:ss";
    }

    /**
     * Devuelve el separador decimal a usar en formator numéricos.
     *
     * @param string $default    Valor por defecto del separador decimal.
     * @return string            Separador decimal.
     */
    public function getTrendixDecimalSeparator($default = ',')
    {
        return $this->session->get(TrendixBaseController::TRENDIX_DECIMAL_SEPARATOR, $default);
    }

    /**
     * Devuelve el separador de miles a usar en formator numéricos.
     *
     * @param string $default    Valor por defecto del separador de miles.
     * @return string            Separador de miles.
     */
    public function getTrendixThousandsSeparator($default = '.')
    {
        return $this->session->get(TrendixBaseController::TRENDIX_THOUSANDS_SEPARATOR, $default);
    }

    /**
     * Añade el servidor a una URL
     *
     * @param string $url
     * @return string
     */
    public function getTrendixUrl($url)
    {
        if($url[0] != '/') {
            $url = '/' . $url;
        }
        $scheme = $this->router->getContext()->getScheme();
        $host = $this->router->getContext()->getHost();
        return $scheme . "://" . $host . $url;
    }

    /**
     * Formatea una fecha con el formato Trendix.
     *
     * @param \DateTime $date    Fecha origen.
     * @return string            Fecha formateada.
     */
    public function formatTrendixDate($date)
    {
        if($date == null) {
            return null;
        }

        $dateFormat = $this->getTrendixDateFormat();
        return $date->format($dateFormat);
    }

    /**
     * Formatea una fecha y hora con el formato Trendix.
     *
     * @param \DateTime $datetime    Fecha y hora origen.
     * @return string                Fecha y hora formateadas.
     */
    public function formatTrendixDateTime($datetime)
    {
        if($datetime == null) {
            return null;
        }

        $datetimeFormat = $this->getTrendixDateTimeFormat();
        return $datetime->format($datetimeFormat);
    }

    /**
     * Formatea una hora con el formato Trendix.
     *
     * @param \DateTime $datetime    Fecha y hora origen.
     * @return string                Fecha y hora formateadas.
     */
    public function formatTrendixTime($datetime)
    {
        if($datetime == null) {
            return null;
        }

        $datetimeFormat = $this->getTrendixTimeFormat();
        return $datetime->format($datetimeFormat);
    }

    /**
     * Formatea un número con el formato Trendix.
     *
     * @param float/int $number Número formatear.
     * @param int $decimals        Número de decimales.
     * @return string            Número formateado.
     */
    public function formatTrendixNumber($number, $decimals = 2)
    {
        if($number == null) {
            return 0;
        }

        if(!$this->validateNumber($number)){
            return $number;
        }

        $decimalSep        = $this->getTrendixDecimalSeparator();
        $thousandsSep    = $this->getTrendixThousandsSeparator();

        return number_format($number, $decimals, $decimalSep, $thousandsSep);
    }

    /**
     * Formatea un importe con el formato Trendix.
     *
     * @param float/int $amount    Número formatear.
     * @param string $currency    Nombre de la moneda.
     * @param int $decimales Numero de Decimales
     * @return string            Número formateado.
     */
    public function formatTrendixAmount($amount, $currency = null, $decimales = null)
    {
        if($amount == null) {
            return 0;
        }

        if (null === $decimales) {
            $decimales = $this->session->get(TrendixBaseController::TRENDIX_NUMBER_DECIMAL, 2);
        }

        if(!$this->validateNumber($amount)){
            return $amount;
        }

        $amount = $this->formatTrendixNumber($amount, $decimales);
        return trim($amount . ' ' . $currency);
    }

    private function validateNumber($number){
        return is_numeric($number);
    }

    /**
     * Formatea un texto para salida en pantalla permitiendo algunas etiquetas HTML:
     * <b> <i> <u> <strong> <em> <br> <p> <ul> <ol> <li>
     *
     * @param string $text    Texto a formatear
     * @return string        Texto formateado.
     */
    public function formatLongText($text)
    {
        if(null == $text) {
            return null;
        }

        $text = strip_tags($text, "<b><i><u><strong><em><br><hr><p><ul><ol><li><table><tr><td><th>");

        $search = array(
            "\r\n",            "<br>",
            "\r",            "<br>",
            "\n",            "<br>",
            "\t",            " ",
            "  ",            " ",
            "<br> ",        "<br>",
            " <br>",        "<br>",
            "<br><br><br>",    "<br><br>"
        );

        for($i = 0; $i < count($search); $i += 2) {
            do {
                $text = str_replace($search[$i], $search[$i+1], $text);
            }
            while(false !== strpos($text, $search[$i]));
        }

        $tags = array(
            "<p>",        "</p>",
            "<ul>",        "</ul>",
            "<ol>",        "</ol>",
            "<li>",        "</li>",
            "<table>",    "</table>",
            "<tr>",        "</tr>",
            "<th>",        "</th>",
            "<td>",        "</td>",
            "<hr>"
        );

        foreach($tags as $tag) {
            do {
                $str = $tag . "<br>";
                $text = str_replace($str, $tag, $text);
            }
            while(false !== strpos($text, $str));
            do {
                $str = "<br><br>" . $tag;
                $text = str_replace($str, "<br>" . $tag, $text);
            }
            while(false !== strpos($text, $str));
        }

        while(0 === strpos($text, "<br>")) {
            $text = substr($text, 4);
        }

        return $text;
    }

    /**
     * Formatea un texto en forma de lista, usando los retornos del carro como separadores.
     *
     * @param string $text        Texto a formatear.
     * @param bool $bullets        Indica si usar etiquetas <ul> y <li>.
     * @param int  $limite        Número máximo de líneas. Añade [++] al final.
     * @return string            Texto formateado.
     */
    public function formatTextList($text, $bullets = false, $limite = 0)
    {
        if(null == $text) {
            return null;
        }

        $text = strip_tags($text);
        $text = str_replace("\t",    "\n",    $text);
        $text = str_replace("\r",    "\n",    $text);
        while(false !== strpos("\n\n", $text)) {
            $text = str_replace("\n\n",    "\n",    $text);
        }
        $text = trim($text, "\n");

        $pos = strpos($text, "\n");
        if(false === $pos) {
            return $text;
        }
        else {

            $items = explode("\n", $text);
            $text = '';
            $count = 0;

            foreach($items as $item) {
                $item = trim($item);
                if($item != "") {
                    if($bullets) {
                        $text .= '<li>' . $item . '</li>';
                    }
                    else {
                        if($text != '') {
                            $text .= '<br>';
                        }
                        $text .= $item;
                    }
                }

                ++$count;
                if($limite > 0 && $count >= $limite) {
                    $text .= ' [++]';
                    break;
                }
            }

            if ($text == '') {
                $text = null;
            }
            else if($bullets) {
                $text = '<ul>' . $text . '</ul>';
            }

            return $text;
        }
    }

    /**
     * Ordena una colección de objetos por una o más propiedades. Sirve principalmente para ordenar relaciones Doctrine
     * de tipo OneToMany directamente desde Twig (o desde cualquier lado al estar declarado como servicio).
     *
     * Se permite ordenar por una sola propiedad o por varias. Incluso se permite ordenar por subpropiedades. Por
     * ejemplo supongamos que queremos ordenar una lista de facturas. Para ordenar por identificador el valor de
     * properties sería "id". En cambio para ordenar por id de cliente sería "cliente.id". Y por último si quisiéramos
     * ordenar por apellidos y nombre del cliente sería ["cliente.apellidos", "cliente.nombre"].
     *
     * Las propiedades deben siempre existir en los objetos de la lista. Es decir, si ponemos "cliente.id" los objetos
     * de la colección deben tener la propiedad getCliente() y el objeto devuelve por getCliente() debe tener la
     * propiedad getId().
     *
     * @param ArrayCollection $collection
     * @param string|array $properties
     * @return array
     */
    public function sortCollection($collection, $properties) {

        if(method_exists($collection, 'getValues')) {
            $objects = $collection->getValues();
        }
        elseif(is_array($collection)) {
            $objects = $collection;
        }
        else {
            $objects = array( );
        }

        if($properties == null) {
            return $objects;
        }
        else if(is_string($properties)) {
            $property = $properties;
            $properties = array( );
            $properties[] = $property;
        }

        if(is_array($properties)) {

            usort($objects, function ($a, $b) use ($properties) {

                foreach($properties as $property) {

                    $objA = $a;
                    $objB = $b;

                    if(is_string($property)) {

                        $property = explode('.', $property);
                        foreach($property as $method) {

                            if($objA === null || $objB === null) {
                                break;
                            }

                            $getter = 'get' . $method;

                            if(method_exists($objA, $method)) {
                                $objA = $objA->$method();
                            }
                            elseif(method_exists($objA, $getter)) {
                                $objA = $objA->$getter();
                            }
                            else {
                                $objA = null;
                            }

                            if(method_exists($objB, $method)) {
                                $objB = $objB->$method();
                            }
                            elseif(method_exists($objB, $getter)) {
                                $objB = $objB->$getter();
                            }
                            else {
                                $objB = null;
                            }
                        }
                    }

                    if($objA !== null && $objB === null) {
                        return -1;
                    }
                    elseif($objA === null && $objB !== null) {
                        return 1;
                    }
                    elseif($objA < $objB) {
                        return -1;
                    }
                    elseif($objA > $objB) {
                        return 1;
                    }
                }

                return 0;
            });
        }

        return $objects;
    }


    public function formatExceltTextList($text, $bullets = '●')
    {
        if(null == $text) {
            return null;
        }

        $text = strip_tags($text);
        $pos = strpos($text, "\n");

        if(false === $pos) {
            return $text;
        }
        else {

            $items = explode("\n", $text);
            $text = '';
            $i = 0;
            $total = count($items);
            foreach ($items as $item) {

                $item = trim($item);
                if($item != "") {

                    /*$text .=  'char(165) & "' .$item .'" & char(10) ';
                    if($i < $total -1 ){
                        $text .= ' & ';
                    }*/
                    $text .=  $bullets ." " . $item  ."\r";
                    //$i++;

                }
            }


            return $text;
        }
    }

    /**
     * @param $value
     * @return bool
     */
    public function isNumeric($value){
        return is_numeric($value);
    }

    /**
     * @param $value
     * @return bool
     */
    public function isString($value){
        return is_string($value);
    }

    /**
     * @param $value
     * @return bool
     */
    public function isDate($value){
        return $value instanceof \DateTime;
    }

    public function ksort($array) {
        if (!is_array($array)) {
            return $array;
        }

        ksort($array);

        return $array;
    }

    /**
     * Obetiene el nombre de la clase con su namespace
     * @param $object
     * @return null|string
     */
    public function getclass($object) {
        if (!is_object($object)) {
            return null;
        }

        return get_class($object);
    }

    /**
     * Transforma un string en formato CamelCase
     * @param $string
     * @return string
     */
    public function camelcase($string)
    {
        return str_replace('_', '', ucwords($string, '_'));
    }

    /**
     * Transforma un DateTime en un string de hace.  .
     * @param \DateTime $date
     * @return string
     */
    public function timeago($date)
    {
        if($date == null) {
            return '';
        }
        $humanized = Dates::humaninzeTimeAgo($date);
        $trans = $this->container->get('translator');
        // {{ mensaje.timeago.text|transchoice(mensaje.timeago.number, {'%number%': mensaje.timeago.number}) }}
        return $trans->transChoice($humanized['text'], $humanized['number'], ['%number%' => $humanized['number']]);
    }

    /**
     * Transforma un número en una secuencia de letras
     * @param $number
     * @return string
     */
    public function letter($number)
    {
        return Numeration::getLetterFromNumber($number);
    }

}
