<?php


namespace Trendix\AdminBundle\Component\Lista;


interface ListaBuilderInterface
{
    /**
     * Adds a new field to this group. A field must have a unique name within
     * the group. Otherwise the existing field is overwritten.
     *
     * If you add a nested group, this group should also be represented in the
     * object hierarchy.
     *
     * @param string|int|ListaBuilderInterface $child
     * @param string|ListaTypeInterface        $type
     * @param array                           $options
     *
     * @return ListaBuilderInterface The builder object.
     */
    public function add($child, $type = null, array $options = array());

    /**
     *
     * Add Action to actions list incude click event on row
     *
     * @param $action
     * @param null $icon
     * @param array $options
     * @param null $text
     * @return mixed
     */
    public function addAction($action, $icon = null, $options = array(), $text = null);
}