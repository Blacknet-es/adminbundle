<?php

namespace Trendix\AdminBundle\EventListener;

/**
 * Class DiscriminatorEntry
 * added Target Annotation that it only should have effect on class annotations
 * if you want to use an attribute, you need to define it, and the type, else you have an exception
 * @Annotation
 * @Target({"CLASS"})
 * @Attributes({
 *   @Attribute("value", type = "string"),
 * })
 */
class DiscriminatorEntry {
    private $value;

    public function __construct( array $data ) {
        $this->value = $data['value'];
    }

    public function getValue() {
        return $this->value;
    }
}