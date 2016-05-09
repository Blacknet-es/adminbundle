<?php

namespace Trendix\AdminBundle\Component\Lista;


class ActionType
{
    /**
     * @var string
     */
    protected $action;

    /**
     * @var string
     */
    protected $icon;

    /**
     * @var array
     */
    protected $options;

    public function __construct($action, $icon, $options)
    {
        $this->action = $action;
        $this->icon = $icon;
        $this->options = array_merge($this->getDefaultOptions(), $options);
    }

    /**
     * @return string
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * @return string
     */
    public function getIcon()
    {
        return $this->icon;
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    public function getDefaultOptions()
    {
        return array(
            'route' => null,
            'route_params' => array(),
            'route_list_params' => array(),
            'url' => null
        );
    }
}