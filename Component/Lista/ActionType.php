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
     * @var string
     */
    protected $text;

    /**
     * @var array
     */
    protected $options;

    public function __construct($action, $icon, $text, $options)
    {
        $this->action = $action;
        $this->icon = $icon;
        $this->options = array_merge($this->getDefaultOptions(), $options);
        $this->text = $text;
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
     * @return string
     */
    public function getText()
    {
        return $this->text;
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