<?php

namespace Trendix\AdminBundle\Utility;

use Symfony\Component\Translation\Translator;
use Symfony\Component\Translation\Loader\ArrayLoader;

/**
 * Utilidades con fechas
 */
class Dates {
    public static function getDateWithMilisecs()
    {
        $date = new \DateTime();
        $milisecs = microtime();
        $time = explode(' ', $milisecs);
        $date->setTimestamp($time[1]);
        $milisecs = substr(str_replace('0.', '', $time[0]), 0, 6);
        $date = $date->format('Y-m-d H:i:s').'.'.$milisecs;

        return $date;
    }

    public static function getDateTimeWithMilisecs()
    {
        $date = self::getDateWithMilisecs();
        return new \DateTime($date);
    }

    /**
     *
     * @param \DateTime $time
     * @return Array
     *
     * Twig Usage: elem.timeago.text|transchoice(elem.timeago.number, {'%number%': elem.timeago.number})
     */
    public static function humaninzeTimeAgo (\DateTime $time)
    {
        $time = time() - $time->getTimestamp(); // to get the time since that moment

        if ($time < 1) {
            $time = 1;
        }

        $tokens = array (
            31536000 => 'año',
            2592000 => 'mes',
            //604800 => 'semana',
            86400 => 'día',
            3600 => 'hora',
            60 => 'minuto',
            1 => 'segundo'
        );

        foreach ($tokens as $unit => $text) {
            if ($time < $unit) {
                continue;
            }
            $numberOfUnits = floor($time / $unit);
            $cadena = 'hace %number% '.$text;
            return array('text' => $cadena, 'number' => $numberOfUnits);
        }
    }

    /**
     * Devuelve un array con el formato UTC para javascript
     * @param \DateTime $fecha
     * @return array
     */
    public static function getArrayUTC(\DateTime $fecha)
    {
        return array(
            'y' => intval($fecha->format('Y')),
            'm' => intval($fecha->format('m')) - 1,
            'd' => intval($fecha->format('d')),
            'h' => intval($fecha->format('H')),
            'i' => intval($fecha->format('i')),
            's' => intval($fecha->format('s')),
            'u' => self::base($fecha->format('u'), 1000)
        );
    }

    /**
     * Devuelve el valor $valor en base $base
     * @param $valor
     * @param $base
     * @return int
     */
    public static function base($valor, $base)
    {
        $valor = intval($valor);
        while ($valor >= $base) {
            $valor = intval($valor/10);
        }

        return $valor;
    }

    /**
     * Devuelve la diferencia entre dos fechas en la escala marcada
     * @param $date1
     * @param $date2
     * @param string $scale
     * @return int
     */
    public static function diff ($date1, $date2, $scale = 'segundos')
    {

        $diff = $date1->diff($date2);

        if ($scale == 'dias') {
            return $diff->days;
        }

        $diffInt = $diff->days * 24 * 60 * 60;
        $diffInt += $diff->h * 60 * 60;
        if ($scale == 'horas') {
            return intval($diffInt / 3600);
        }
        $diffInt += $diff->i * 60;
        if ($scale == 'minutos') {
            return intval($diffInt / 60);
        }
        $diffInt += $diff->s;

        return $diffInt;
    }

    /**
     * Devuelve true si es aterior y false si es posterior (compara milisegundos)
     * @param $date1
     * @param $date2
     *
     * @return boolean
     */
    public static function esAnterior($date1, $date2)
    {
        $anterior = $date1 < $date2;
        if ($date1 == $date2 && !$anterior) {
            $ms1 = $date1->format('u');
            $ms2 = $date2->format('u');
            if (floatval($ms1) < floatval($ms2)) {
                $anterior = true;
            }
        }

        return $anterior;
    }
}
