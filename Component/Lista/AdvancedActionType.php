<?php

namespace Trendix\AdminBundle\Component\Lista;


class AdvancedActionType extends ActionType
{

    /**
     * @var string
     */
    protected $view = '';

    /**
     * @var array
     */
    protected $viewData = [];

    public function __construct($action, $icon, $text, $options, $view, $viewData)
    {
        parent::__construct($action, $icon, $text, $options);
        $this->view = $view;
        $this->viewData = $viewData;
    }

    /**
     * @return string
     */
    public function getView(): ?string
    {
        return $this->view;
    }

    /**
     * @return array
     */
    public function getViewData(): ?array
    {
        return $this->viewData;
    }


}
