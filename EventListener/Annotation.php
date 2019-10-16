<?php
/**
 * Created by PhpStorm.
 * User: jose
 * Date: 27/09/18
 * Time: 11:05
 */

namespace Trendix\AdminBundle\EventListener;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\SimpleAnnotationReader;

/**
 * Class Annotation
 */
class Annotation
{

    /**
     * @var SimpleAnnotationReader
     */
    public static $reader;

    public static function getAnnotationsForClass( $className ) {
        $class = new \ReflectionClass( $className );
        return Annotation::$reader->getClassAnnotations( $class );
    }

}
//set the reader. Externally, because you cannot set a static property as a return value of a function
Annotation::$reader = new AnnotationReader();
//the function with the default namespace does not exist anymore
//Annotation::$reader->setDefaultAnnotationNamespace( __NAMESPACE__ . "" );