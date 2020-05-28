<?php


namespace Trendix\AdminBundle\Component\Lista;


//use Trendix\AdminBundle\Component\Lista\DataType\DateType;
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
     * @var array
     */
    protected $filters = array();

    /**
     * @var array
     */
    protected $bulkActions = array();


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
     * @param null $text
     * @param array $options
     * @return $this
     */
    public function addAction($action, $icon = null, $options = array(), $text = null)
    {
        $this->actions[$action] = $this->createAction($action, $icon, $text, $options);
        return $this;
    }

    /**
     * @param $action
     * @param null $text
     * @param string $view Template which contains the code to show at the action modal
     * @param array $viewData Data to pass to the template
     * @return $this
     */
    public function addAdvancedAction($action, $view = '', $viewData = [])
    {
        $this->actions[$action] = $this->createAdvancedAction($action, null, '', [], $view, $viewData);
        return $this;
    }

    /**
     * @param $action
     * @param null $text
     * @param array $options
     * @return $this
     */
    public function addBulkAction($action, $options = array(), $text = null)
    {
        $this->bulkActions[$action] = $this->createBulkAction($action, null, $text, $options);
        return $this;
    }

    /**
     * @param $action
     * @param null $text
     * @param string $view In case this is an advanced bulk action, template which contains the code to show at the action modal
     * @param array $viewData In case this is an advanced bulk action, Data to pass to the template
     * @return $this
     */
    public function addAdvancedBulkAction($action, $text = null, $view = '', $viewData = [])
    {
        $this->bulkActions[$action] = $this->createBulkAction($action, null, $text, [], $view, $viewData);
        return $this;
    }

    /**
     *
     * Add Action to actions list incude click event on row
     *
     * @param string $field
     * @param string $label
     * @param string $type
     * @param array $choices
     * @param string|null $value
     * @return void
     */
    public function addFilter($field, $label, $type = 'text', $choices = array(), $value = '')
    {
        $this->filters[$field] = [
            'value' => $value,
            'type' => $type,
            'label' => $label,
            'choices' => $choices
        ];
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
            return new \Trendix\AdminBundle\Component\Lista\DataType\TextType($child, $options);
        }

        if ($type == 'date') {
            return new \Trendix\AdminBundle\Component\Lista\DataType\DateType($child, $options);
        }

        if ($type == 'boolean') {
            return new \Trendix\AdminBundle\Component\Lista\DataType\BooleanType($child, $options);
        }

        if ($type == 'money') {
            return new \Trendix\AdminBundle\Component\Lista\DataType\MoneyType($child, $options);
        }

        if ($type == 'percent') {
            return new \Trendix\AdminBundle\Component\Lista\DataType\PercentType($child, $options);
        }

        if ($type == 'number') {
            return new \Trendix\AdminBundle\Component\Lista\DataType\NumberType($child, $options);
        }


        $className = '\\'.$type;
        $dataType = new $className($child, $options);
        return $dataType;

        throw new \Exception('Data Type not exists in ListaBuilder Data Type list');
    }

    /**
     * @param $action
     * @param $icon
     * @param null $text
     * @param array $options
     * @return ActionType
     */
    private function createAction($action, $icon, $text = null, $options = array())
    {
        return new ActionType($action, $icon, $text, $options);
    }

    /**
     * @param $action
     * @param $icon
     * @param null $text
     * @param array $options
     * @return ActionType
     */
    private function createBulkAction($action, $icon, $text = null, $options = array(), $view = '', $viewData = [])
    {
        return new BulkActionType($action, $icon, $text, $options, $view, $viewData);
    }

    /**
     * @param $action
     * @param $icon
     * @param null $text
     * @param array $options
     * @param string $view Template which contains the code to show at the action modal
     * @param array $viewData Data to pass to the template
     * @return ActionType
     */
    private function createAdvancedAction($action, $icon, $text, $options, $view, $viewData = [])
    {
        return new AdvancedActionType($action, $icon, $text, $options, $view, $viewData);
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
     * @return array
     */
    public function getFilters()
    {
        return $this->filters;
    }

    /**
     * @return array
     */
    public function getBulkActions()
    {
        return $this->bulkActions;
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
