<?php
/**
 * Created by PhpStorm.
 * User: jose
 * Date: 27/09/18
 * Time: 10:54
 */

namespace Trendix\AdminBundle\EventListener;


class DiscriminatorListener implements Doctrine\Common\EventSubscriber {
    private $driver; // Doctrines Metadata Driver
    private $map; // Our temporary map for calculations
    private $cachedMap; // The cached map for fast lookups

    const ENTRY_ANNOTATION = '\Trendix\AdminBundle\EventListener\DiscriminatorEntry';
    /**
     *
     * @param $class
     * @return array
     */
    public static function getSubClasses($class){
        $subclasses = array();

        foreach(get_declared_classes() as $potentialSubclass)
        {

            $reflection = new \ReflectionClass($potentialSubclass);
            if($reflection ->isSubclassOf($class)){
                $subclasses[] = $potentialSubclass;
            }

        }
        return $subclasses;
    }

    public function getSubscribedEvents() {
        return Array( Doctrine\ORM\Events::loadClassMetadata );
    }

    public function __construct( Doctrine\ORM\EntityManager $db ) {
        $this->driver = $db->getConfiguration()
            ->getMetadataDriverImpl();
        $this->cachedMap = Array();
    }

    public function loadClassMetadata( Doctrine\ORM\Event\LoadClassMetadataEventArgs $event ) {
        // Reset the temporary calculation map and get the classname
        $this->map = Array();
        $class = $event->getClassMetadata()->name;

        // Did we already calculate the map for this element?
        if( array_key_exists( $class, $this->cachedMap ) ) {
            $this->overrideMetadata( $event, $class );
            return;
        }

        // Do we have to process this class?
        if( count( $event->getClassMetadata()->discriminatorMap ) < 2 && $this->extractEntry( $class ) ) {
            // Now build the whole map
            $this->checkFamily( $class );
        } else {
            // Nothing to do…
            return;
        }

        // Create the lookup entries
        $dMap = array_flip( $this->map );
        foreach( $this->map as $cName => $discr ) {
            $this->cachedMap[$cName]['map'] = $dMap;
            $this->cachedMap[$cName]['discr'] = $this->map[$cName];
        }

        // Override the data for this class
        $this->overrideMetadata( $event, $class );
    }

    private function extractEntry( $class ) {
        $annotations = \Trendix\RankBundle\EventListener\Annotation::getAnnotationForClass( $class );
        $success = false;
        foreach($annotations as $key => $annotation){
            if(get_class($annotation) == self::ENTRY_ANNOTATION){
                //TODO check for duplicates
                $this->map[$class] = $annotation->getValue();
                $success = true;
            }
        }
        /*
        if( array_key_exists( self::ENTRY_ANNOTATION, $annotations ) ) {
            $value = $annotations[self::ENTRY_ANNOTATION]->getValue();

            if( in_array( $value, $this->map ) ) {
                throw new Exception( "Found duplicate discriminator map entry '" . $value . "' in " . $class );
            }

            $this->map[$class] = $value;
            $success = true;
        }*/

        return $success;
    }

    private function checkFamily( $class ) {
        $rc = new ReflectionClass( $class );
        $parent = $rc->getParentClass()->name;

        if( $parent !== false ) {
            // Also check all the children of our parent
            $this->checkFamily( $parent );
        } else {
            // This is the top-most parent, used in overrideMetadata
            $this->cachedMap[$class]['isParent'] = true;

            // Find all the children of this class
            $this->checkChildren( $class );
        }
    }

    private function checkChildren( $class ) {
        foreach( static::getSubClasses($class) as $name ) {
            $cRc = new ReflectionClass( $name );
            $cParent = $cRc->getParentClass()->name;

            // Haven't done this class yet? Go for it.
            if( ! array_key_exists( $name, $this->map )
                && $cParent == $class && $this->extractEntry( $name ) ) {
                $this->checkChildren( $name );
            }
        }
    }

    private function overrideMetadata( Doctrine\ORM\Event\LoadClassMetadataEventArgs $event, $class ) {
        // Set the discriminator map and value
        $event->getClassMetadata()->discriminatorMap = $this->cachedMap[$class]['map'];
        $event->getClassMetadata()->discriminatorValue = $this->cachedMap[$class]['discr'];

        // If we are the top-most parent, set subclasses!
        if( isset( $this->cachedMap[$class]['isParent'] )
            && $this->cachedMap[$class]['isParent'] === true ) {
            $subclasses = $this->cachedMap[$class]['map'];
            unset( $subclasses[$this->cachedMap[$class]['discr']] );
            $event->getClassMetadata()->subClasses =
                array_values( $subclasses );
        }
    }
}