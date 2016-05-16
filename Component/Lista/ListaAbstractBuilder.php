<?php


namespace Trendix\AdminBundle\Component\Lista;


use Trendix\AdminBundle\Component\Lista\DataType\DateType;
use Trendix\AdminBundle\Component\Lista\DataType\TextType;

abstract class ListaAbstractBuilder implements ListaBuilderInterface
{
    /**
     * @var array
     */
    protected $childs = array();

    /**
     * @var array
     */
    protected $actions = array();

    /**
     * {@inheritdoc}
     */
    public function add($child, $type = null, array $options = array())
    {

        $this->childs[$child] = $this->createChild($child, $type, $options);
        return $this;
    }

    /**
     * @param $action
     * @param null $icon
     * @param array $options
     * @return $this
     */
    public function addAction($action, $icon = null, $options = array())
    {
        $this->actions[$action] = $this->createAction($action, $icon, $options);
        return $this;
    }


    /**
     * @param $child
     * @param null $type
     * @param array $options
     * @return DateType|TextType
     * @throws \Exception
     */
    private function createChild($child, $type = null, $options = array())
    {
        if (null === $type || $type == 'text') {
            return new TextType($child, $options);
        }

        if ($type == 'date') {
            return new DateType($child, $options);
        }

        throw new \Exception('Data Type not exists in ListaBuilder Data Type list');
    }

    /**
     * @param $action
     * @param $icon
     * @param array $options
     * @return ActionType
     */
    private function createAction($action, $icon, $options = array())
    {
        return new ActionType($action, $icon, $options);
    }

    /**
     * @return array
     */
    public function getChilds()
    {
        return $this->childs;
    }

    /**
     * @return array
     */
    public function getActions()
    {
        return $this->actions;
    }

    /**
     * @return null
     */
    public function getPrimaryAction()
    {
        if (isset($this->actions['primary'])) {
            return $this->actions['primary'];
        }

        return null;
    }
}