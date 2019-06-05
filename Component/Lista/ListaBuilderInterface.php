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

    /**
     *
     * Add Bulk Action to actions list
     *
     * @param $action
     * @param null $icon
     * @param array $options
     * @param null $text
     * @return mixed
     */
    public function addBulkAction($action, $icon = null, $options = array(), $text = null);

    /**
     *
     * Add Advanced Action to actions list
     *
     * @param $action
     * @param null $icon
     * @param array $options
     * @param null $text
     * @param string $view Template which contains the code to show at the action modal
     * @param array $viewData Data to pass to the template
     * @return mixed
     */
    public function addAdvancedAction($action, $icon = null, $options = array(), $text = null, $view, $viewData);
}
